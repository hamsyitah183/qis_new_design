<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\Activitylog\Models\Activity;

class LogUserLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event)
    {
        $user = $event->user;
        $guard = $event->guard ?? 'default';

        if (!$user instanceof Model) {
            return; // ensure $user is a model
        }

        $username = $user->name ?? $user->fullname ?? 'Unknown user';

        activity()
            ->tap(function (Activity $activity) {
                $activity->log_name = 'user_activity';
            })
            ->causedBy($user)
            ->event('login')
            ->performedOn($user)
            ->withProperties([
                'ip' => request()->ip(),
                'guard' => $guard,
            ])
            ->log("{$username} logged in to the system ({$guard} guard).");
    }
}
