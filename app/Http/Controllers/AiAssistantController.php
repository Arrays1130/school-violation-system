<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiUsageLog;
use App\Services\AiEmbeddingService;
use App\Services\AiService;
use App\Support\AiAssistantContext;
use App\Support\SchoolSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiAssistantController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(Request $request)
    {
        $this->authorize('use-ai-assistant');

        $hasGemini = (bool) config('ai.api_key');
        $conversation = $this->resolveConversation($request);
        $embeddingStats = app(AiEmbeddingService::class)->stats();
        $pageContext = AiAssistantContext::fromRequest($request);

        $initialMessages = $conversation->messages()
            ->latest()
            ->limit(40)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (AiMessage $message) => [
                'role' => $message->role === 'assistant' ? 'bot' : 'user',
                'content' => $message->content,
                'sources' => $message->sources ?? [],
                'mode' => $message->mode,
                'usageLogId' => $message->ai_usage_log_id,
            ]);

        return inertia('AiAssistant/Index', [
            'provider' => $hasGemini ? 'Gemini AI' : 'Handbook Search',
            'providerMode' => $hasGemini ? 'gemini' : 'handbook',
            'schoolName' => SchoolSettings::get('school_name', config('school.name', 'I-Link CST')),
            'conversationId' => $conversation->id,
            'initialMessages' => $initialMessages,
            'vectorSearchReady' => $embeddingStats['total_chunks'] > 0,
            'pageContext' => $pageContext,
            'initialPrompt' => AiAssistantContext::defaultPrompt($pageContext),
        ]);
    }

    public function chat(Request $request)
    {
        $this->authorize('use-ai-assistant');

        $validated = $request->validate([
            'message' => 'required|string|min:2|max:2000',
            'conversation_id' => 'nullable|integer|exists:ai_conversations,id',
            'page_context' => 'nullable|array',
            'page_context.student_id' => 'nullable|integer',
            'page_context.case_id' => 'nullable|integer',
        ]);

        $message = $validated['message'];
        $conversation = $this->resolveConversation($request, $validated['conversation_id'] ?? null);
        $history = $this->loadServerHistory($conversation);
        $pageContext = $this->resolvePageContext($request, $validated['page_context'] ?? null);
        $result = $this->aiService->processChat($message, $request->user(), $history, $pageContext);

        $usageLog = $this->logUsage(
            $request,
            $message,
            strlen(strip_tags($result['reply'] ?? '')),
            'chat',
            $conversation->id
        );

        $this->persistExchange(
            $conversation,
            $message,
            strip_tags($result['reply'] ?? ''),
            $result['sources'] ?? [],
            $result['mode'] ?? 'handbook',
            $usageLog?->id
        );

        return response()->json([
            'reply' => $result['reply'] ?? '',
            'sources' => $result['sources'] ?? [],
            'mode' => $result['mode'] ?? 'handbook',
            'conversationId' => $conversation->id,
            'usageLogId' => $usageLog?->id,
        ]);
    }

    public function stream(Request $request)
    {
        $this->authorize('use-ai-assistant');

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|integer|exists:ai_conversations,id',
            'page_context' => 'nullable|array',
            'page_context.student_id' => 'nullable|integer',
            'page_context.case_id' => 'nullable|integer',
        ]);

        $message = $validated['message'];
        $user = $request->user();
        $conversation = $this->resolveConversation($request, $validated['conversation_id'] ?? null);
        $history = $this->loadServerHistory($conversation);
        $pageContext = $this->resolvePageContext($request, $validated['page_context'] ?? null);

        return response()->stream(function () use ($message, $history, $user, $conversation, $request, $pageContext) {
            $send = function (array $payload) {
                echo 'data: '.json_encode($payload)."\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };

            $sendDone = function () use ($send) {
                $send(['done' => true]);
            };

            $responseLength = 0;
            $botReply = '';
            $sources = [];
            $mode = 'handbook';
            $usageLog = null;

            try {
                set_time_limit(180);

                $result = $this->aiService->streamChat(
                    $message,
                    function (string $text) use ($send, &$responseLength, &$botReply) {
                        $responseLength += strlen($text);
                        $botReply .= $text;
                        $send(['text' => $text]);
                    },
                    $user,
                    $history,
                    $pageContext
                );

                $sources = $result['sources'] ?? [];
                $mode = $result['mode'] ?? 'handbook';

                if (! empty($sources)) {
                    $send(['sources' => $sources]);
                }

                $usageLog = $this->logUsage(
                    $request,
                    $message,
                    min($responseLength, 65535),
                    'stream',
                    $conversation->id
                );

                $send(['meta' => [
                    'mode' => $mode,
                    'conversationId' => $conversation->id,
                    'usageLogId' => $usageLog?->id,
                ]]);

                $this->persistExchange(
                    $conversation,
                    $message,
                    $botReply,
                    $sources,
                    $mode,
                    $usageLog?->id
                );

                $sendDone();
            } catch (\Throwable $e) {
                Log::error('AI Stream error: '.$e->getMessage());

                $send(['text' => "\n\n⚠️ **Connection issue with the AI core.** Attempting local handbook search...\n\n"]);

                try {
                    $fallback = $this->aiService->processChat($message, $user, $history, $pageContext);
                    $reply = strip_tags($fallback['reply'] ?? '');
                    if (! empty($reply)) {
                        $responseLength += strlen($reply);
                        $botReply .= $reply;
                        $send(['text' => $reply]);
                        $sources = $fallback['sources'] ?? [];
                        $mode = $fallback['mode'] ?? 'handbook';
                        if (! empty($sources)) {
                            $send(['sources' => $sources]);
                        }

                        $usageLog = $this->logUsage(
                            $request,
                            $message,
                            min($responseLength, 65535),
                            'stream',
                            $conversation->id
                        );

                        $send(['meta' => [
                            'mode' => $mode,
                            'conversationId' => $conversation->id,
                            'usageLogId' => $usageLog?->id,
                        ]]);

                        $this->persistExchange(
                            $conversation,
                            $message,
                            $botReply,
                            $sources,
                            $mode,
                            $usageLog?->id
                        );
                    } else {
                        $send(['text' => 'Hindi ko mahanap ang sagot sa handbook. Subukan mong i-rephrase ang tanong.']);
                    }
                } catch (\Throwable $inner) {
                    $send(['text' => 'Paumanhin, hindi pa rin available ang AI. Pakisuri ang iyong internet connection o GEMINI_API_KEY sa .env file.']);
                }

                $sendDone();
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    public function feedback(Request $request)
    {
        $this->authorize('use-ai-assistant');

        $validated = $request->validate([
            'usage_log_id' => 'required|integer|exists:ai_usage_logs,id',
            'rating' => 'required|in:1,-1',
        ]);

        $usageLog = AiUsageLog::query()
            ->where('id', $validated['usage_log_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $usageLog->update(['rating' => (int) $validated['rating']]);

        return response()->json(['ok' => true]);
    }

    public function clearConversation(Request $request)
    {
        $this->authorize('use-ai-assistant');

        $validated = $request->validate([
            'conversation_id' => 'nullable|integer|exists:ai_conversations,id',
        ]);

        if (! empty($validated['conversation_id'])) {
            AiConversation::query()
                ->where('id', $validated['conversation_id'])
                ->where('user_id', $request->user()->id)
                ->delete();
        }

        $conversation = AiConversation::create([
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'conversationId' => $conversation->id,
        ]);
    }

    protected function resolvePageContext(Request $request, ?array $payload = null): ?array
    {
        if (is_array($payload) && (! empty($payload['student_id']) || ! empty($payload['case_id']))) {
            $request->merge([
                'student_id' => $payload['student_id'] ?? null,
                'case_id' => $payload['case_id'] ?? null,
            ]);
        }

        return AiAssistantContext::fromRequest($request);
    }

    protected function loadServerHistory(AiConversation $conversation): array
    {
        return $conversation->messages()
            ->latest()
            ->limit(20)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (AiMessage $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->all();
    }

    protected function resolveConversation(Request $request, ?int $conversationId = null): AiConversation
    {
        if ($conversationId) {
            return AiConversation::query()
                ->where('id', $conversationId)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        }

        return AiConversation::query()
            ->where('user_id', $request->user()->id)
            ->latest('updated_at')
            ->first()
            ?? AiConversation::create(['user_id' => $request->user()->id]);
    }

    protected function persistExchange(
        AiConversation $conversation,
        string $userMessage,
        string $assistantReply,
        array $sources,
        string $mode,
        ?int $usageLogId = null
    ): void {
        if ($conversation->title === null) {
            $conversation->update([
                'title' => Str::limit($userMessage, 120),
            ]);
        }

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $userMessage,
        ]);

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $assistantReply,
            'sources' => $sources,
            'mode' => $mode,
            'ai_usage_log_id' => $usageLogId,
        ]);

        $conversation->touch();
    }

    protected function logUsage(Request $request, string $message, int $responseLength, string $channel, ?int $conversationId = null): AiUsageLog
    {
        $usageLog = AiUsageLog::create([
            'user_id' => $request->user()->id,
            'ai_conversation_id' => $conversationId,
            'message' => Str::limit($message, 500),
            'response_length' => min($responseLength, 65535),
            'channel' => $channel,
        ]);

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'channel' => $channel,
                'prompt_preview' => Str::limit($message, 120),
                'conversation_id' => $conversationId,
            ])
            ->log('ai_query');

        return $usageLog;
    }
}
