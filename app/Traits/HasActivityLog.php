<?php

namespace App\Traits;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

trait HasActivityLog
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('user_activity')
            ->logOnly([
                'name', 'email', 'phone', 'position', 'office',
                'no_ic', 'fullname', 'account_type', 'state'
            ])
            ->logOnlyDirty() // only log changed fields
            ->setDescriptionForEvent(function (string $eventName) {
                $name = $this->name ?? $this->fullname ?? 'Unknown user';

                switch ($eventName) {
                    case 'created':
                        return "{$name} has been registered.";
                    case 'updated':
                        return "{$name}'s profile information was updated.";
                    case 'deleted':
                        return "{$name}'s account was deleted.";
                    default:
                        return "Action '{$eventName}' occurred for {$name}.";
                }
            });
    }
}
