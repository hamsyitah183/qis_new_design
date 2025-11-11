<?php

namespace App\Providers;

use App\Listeners\LogUserLogin;

use Illuminate\Auth\Events\Login;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    protected $listen = [
        Login::class => [
            LogUserLogin::class,
        ],

    ];

    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
