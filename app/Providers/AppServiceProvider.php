<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
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
     
    }
}
