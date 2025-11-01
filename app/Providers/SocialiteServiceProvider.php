<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Authentik\Provider;

class SocialiteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Socialite::extend('authentik', function ($app) {
            $config = $app['config']['services.authentik'];
            return Socialite::buildProvider(Provider::class, $config);
        });
    }
}