<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Violation::class, \App\Policies\ViolationPolicy::class);

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
