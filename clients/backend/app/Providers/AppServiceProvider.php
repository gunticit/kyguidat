<?php

namespace App\Providers;

use App\Models\Consignment;
use App\Observers\ConsignmentEmbeddingObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

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
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Register Zalo Socialite provider
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('zalo', \SocialiteProviders\Zalo\Provider::class);
        });

        // RAG: auto re-embed Consignment whenever text-bearing fields change.
        Consignment::observe(ConsignmentEmbeddingObserver::class);
    }
}
