<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

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
        //
        Relation::morphMap([
            'public' => \App\Models\PublicUser::class,
            'internal' => \App\Models\InternalUser::class,
        ]);
        Activity::saving(function (Activity $activity) {
            if (auth()->check() && is_null($activity->causer_id)) {
                $activity->causedBy(auth()->user());
            }
        });

        // Force HTTPS URL generation and trust the Cloudflare Tunnel
        // as a proxy, so asset()/url()/route() and the request's own
        // scheme detection all resolve to https:// instead of http://.
        // Only applied when actually tunneling through a *.trycloudflare.com
        // host, so normal local development (plain http://localhost) is unaffected.
        if (str_contains(request()->getHost(), 'trycloudflare.com')) {
            URL::forceScheme('https');
            request()->server->set('HTTPS', 'on');
        }
    }
}