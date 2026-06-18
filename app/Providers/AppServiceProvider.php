<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        Schema::defaultStringLength(191);

        view()->composer('layouts.app', function ($view) {
            if (auth()->check() && auth()->user()->role === 'rider') {
                $pendingCount = auth()->user()
                    ->rideRequests()
                    ->where('status', 'pending')
                    ->count();
                $view->with('pendingCount', $pendingCount);
            }
        });
    }
}
