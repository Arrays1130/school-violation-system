<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hearing;
use App\Models\Student;
use App\Services\AiService;
use App\Support\DepartmentResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MobilePolicyController extends Controller
{
    public function lookup(Request $request, AiService $aiService)
    {
        Gate::authorize('use-ai-assistant');

        $validated = $request->validate([
            'message' => 'required|string|min:2|max:500',
        ]);

        $result = $aiService->processChat($validated['message'], $request->user(), []);

        return response()->json([
            'reply' => strip_tags($result['reply'] ?? ''),
            'sources' => $result['sources'] ?? [],
            'mode' => $result['mode'] ?? 'handbook',
        ]);
    }
}
