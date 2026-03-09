<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\CompetitionFull;
use App\Listeners\GenerateGroupsAndMatches;
use Illuminate\Support\Facades\URL;


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
      // Force HTTPS en production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
