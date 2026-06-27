<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        Paginator::defaultView('pagination::tailwind');
        Paginator::defaultSimpleView('pagination::simple-tailwind');

        \Illuminate\Support\Facades\Gate::policy(\App\Models\Violation::class, \App\Policies\ViolationPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Student::class, \App\Policies\StudentPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\StudentCase::class, \App\Policies\StudentCasePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Hearing::class, \App\Policies\HearingPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Handbook::class, \App\Policies\HandbookPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\EmailLog::class, \App\Policies\EmailLogPolicy::class);

        \Illuminate\Support\Facades\Gate::define('use-ai-assistant', function (\App\Models\User $user) {
            return $user->isSuperAdmin() || $user->isAdmin() || $user->isDean();
        });

        // Subdirectory support for Livewire (Only for local Laragon environment)
        if (config('app.env') === 'local') {
            \Livewire\Livewire::setUpdateRoute(function ($handle) {
                return \Illuminate\Support\Facades\Route::post('/school%20violation%20system/public/livewire/update', $handle);
            });

            \Livewire\Livewire::setScriptRoute(function ($handle) {
                return \Illuminate\Support\Facades\Route::get('/school%20violation%20system/public/livewire/livewire.js', $handle);
            });
        }

        $this->app->terminating(function () {
            if (config('queue.default') === 'database') {
                try {
                    if (function_exists('exec')) {
                        $artisan = base_path('artisan');
                        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                            pclose(popen("start /B php \"$artisan\" queue:work --max-time=55 --stop-when-empty > NUL 2>&1", "r"));
                        } else {
                            exec("php \"$artisan\" queue:work --max-time=55 --stop-when-empty > /dev/null 2>&1 &");
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore
                }
            }
        });
    }
}
