<?php

namespace App\Http\Middleware;

use App\Services\AiEmbeddingService;
use App\Support\AiAssistantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            // Keep SPA fetch() CSRF in sync after Inertia navigations (avoids HTTP 419).
            'csrf_token' => csrf_token(),
            'auth' => [
                'user' => $request->user(),
                'unreadNotifications' => $request->user() ? $request->user()->unreadNotifications()->latest()->take(10)->get() : [],
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
            ],
            'openCasesCount' => fn () => $request->user()
                ? \App\Models\StudentCase::query()
                    ->forUser($request->user())
                    ->whereNotIn('status', ['Closed', 'Dismissed'])
                    ->count()
                : 0,
            'aiAssistant' => fn () => $request->user() && Gate::allows('use-ai-assistant', $request->user())
                ? [
                    'canUse' => true,
                    'name' => 'Nexus AI',
                    'url' => route('ai-assistant.index'),
                    'provider' => config('ai.api_key') ? 'Gemini AI' : 'Handbook Search',
                    'vectorReady' => app(AiEmbeddingService::class)->isAvailable(),
                ]
                : [
                    'canUse' => false,
                    'name' => 'Nexus AI',
                    'url' => null,
                    'provider' => null,
                    'vectorReady' => false,
                ],
            'pageAiContext' => fn () => AiAssistantContext::fromRequest($request),
        ];
    }
}
