<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiService;

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

        return view('ai-assistant.index');
    }

    public function chat(Request $request)
    {
        $this->authorize('use-ai-assistant');

        $request->validate([
            'message' => 'required|string|min:2',
        ]);

        $message = $request->input('message');
        $result = $this->aiService->processChat($message);

        return response()->json([
            'reply' => $result['reply'] ?? '',
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
            $this->aiService->streamChat($message, function ($chunk) {
                echo "data: " . json_encode(['text' => $chunk]) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
            });
            echo "data: [DONE]\n\n";
            if (ob_get_level() > 0) ob_flush();
            flush();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no', // For Nginx
        ]);
    }
}
