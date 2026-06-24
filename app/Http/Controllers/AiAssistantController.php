<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiService;
use Illuminate\Support\Facades\Log;

class AiAssistantController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        $this->authorize('use-ai-assistant');

        return inertia('AiAssistant/Index');
    }

    public function chat(Request $request)
    {
        $this->authorize('use-ai-assistant');

        $request->validate([
            'message' => 'required|string|min:2',
        ]);

        $message = $request->input('message');
        $result  = $this->aiService->processChat($message);

        return response()->json([
            'reply'   => $result['reply']   ?? '',
            'sources' => $result['sources'] ?? [],
        ]);
    }

    public function stream(Request $request)
    {
        $this->authorize('use-ai-assistant');

        $request->validate([
            'message' => 'required|string',
        ]);

        $message = $request->input('message');

        return response()->stream(function () use ($message) {
            // Helper to send a single SSE chunk
            $send = function (string $text) {
                echo 'data: ' . json_encode(['text' => $text]) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
            };

            $sendDone = function () {
                echo "data: [DONE]\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
            };

            try {
                set_time_limit(180);

                $this->aiService->streamChat($message, $send);

                $sendDone();

            } catch (\Throwable $e) {
                Log::error('AI Stream error: ' . $e->getMessage());

                // Send a graceful error message as a valid SSE event
                // so the frontend can display it instead of a network crash
                $send("\n\n⚠️ **Connection issue with the AI core.** Attempting local handbook search...\n\n");

                // Fallback: try the non-streaming local search
                try {
                    $result = $this->aiService->processChat($message);
                    $reply  = strip_tags($result['reply'] ?? '');
                    if (!empty($reply)) {
                        $send($reply);
                    } else {
                        $send("Hindi ko mahanap ang sagot sa handbook. Subukan mong i-rephrase ang tanong.");
                    }
                } catch (\Throwable $inner) {
                    $send("Paumanhin, hindi pa rin available ang AI. Pakisuri ang Ollama server.");
                }

                $sendDone();
            }
        }, 200, [
            'Cache-Control'    => 'no-cache',
            'Content-Type'     => 'text/event-stream',
            'X-Accel-Buffering'=> 'no',
            'Connection'       => 'keep-alive',
        ]);
    }
}
