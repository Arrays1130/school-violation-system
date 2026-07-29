<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            Mail::extend('google_apps_script', function () {
                return new \App\Mail\Transport\GoogleAppsScriptTransport;
            });
        } catch (\Throwable $e) {
            Log::warning('Could not register google_apps_script mail transport: '.$e->getMessage());
        }

        DB::whenQueryingForLongerThan((int) env('DB_SLOW_QUERY_MS', 500), function ($connection, $event) {
            Log::warning('Slow query detected.', [
                'connection' => $connection->getName(),
                'time_ms' => $event->time,
                'sql' => $event->sql,
            ]);
        });

        Notification::extend('fcm', function ($app) {
            return $app->make(\App\Notifications\Channels\FcmChannel::class);
        });

        Notification::extend('school_mail', function () {
            return new \App\Channels\SchoolMailChannel;
        });

        Paginator::defaultView('pagination::tailwind');
        Paginator::defaultSimpleView('pagination::simple-tailwind');

        \Illuminate\Support\Facades\Gate::policy(\App\Models\Violation::class, \App\Policies\ViolationPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Student::class, \App\Policies\StudentPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\StudentCase::class, \App\Policies\StudentCasePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Hearing::class, \App\Policies\HearingPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Handbook::class, \App\Policies\HandbookPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\EmailLog::class, \App\Policies\EmailLogPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\MessageTemplate::class, \App\Policies\MessageTemplatePolicy::class);

        \Illuminate\Support\Facades\Gate::define('use-ai-assistant', function (\App\Models\User $user) {
            return $user->isSuperAdmin() || $user->isAdmin() || $user->isDean();
        });

        if (config('ai.api_key')) {
            \App\Models\Handbook::saved(function (\App\Models\Handbook $handbook) {
                try {
                    app(\App\Services\AiEmbeddingService::class)->indexHandbook($handbook);
                } catch (\Throwable $e) {
                    report($e);
                }
            });

            \App\Models\Violation::saved(function (\App\Models\Violation $violation) {
                try {
                    app(\App\Services\AiEmbeddingService::class)->indexViolation($violation);
                } catch (\Throwable $e) {
                    report($e);
                }
            });
        }
    }
}
