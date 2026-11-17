<?php

namespace App\Providers;

use App\Events\SubscriptionExpired;
use App\Listeners\HandleSubscriptionExpired;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Di production app berada di belakang Cloudflare Tunnel (HTTP ke Nginx),
        // jadi paksa URL yang dibuat Laravel memakai https.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Event::listen(SubscriptionExpired::class, HandleSubscriptionExpired::class);
    }
}
