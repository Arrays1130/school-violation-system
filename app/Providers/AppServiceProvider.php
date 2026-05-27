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
        //
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

        // Subdirectory support for Livewire
        if (config('app.env') !== 'testing') {
            \Livewire\Livewire::setUpdateRoute(function ($handle) {
                return \Illuminate\Support\Facades\Route::post('/school%20violation%20system/public/livewire/update', $handle);
            });

            \Livewire\Livewire::setScriptRoute(function ($handle) {
                return \Illuminate\Support\Facades\Route::get('/school%20violation%20system/public/livewire/livewire.js', $handle);
            });
        }
    }
}
