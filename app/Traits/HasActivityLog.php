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
                'fullname',
                'email',
                'phone_number',
                'address_1',
                'address_2',
                'district',
                'state',
                'account_type',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {

                // TARGET (the model being changed)
                $targetType = $this instanceof \App\Models\InternalUser
                    ? 'internal user'
                    : 'public user';

                $targetName = $this->name
                    ?? $this->fullname
                    ?? 'Unknown user';

                // CAUSER (who performed the action)
                $auth = authUser(); // your helper
                $causerType = $auth['type'] ?? 'system';
                $causer = $auth['user'] ?? null;

                $causerName = match ($causerType) {
                    'internal' => $causer?->fullname ?? 'Internal Admin',
                    'public'   => $causer?->fullname ?? 'Public User',
                    default    => 'System',
                };

                return match ($eventName) {

                    'created' =>
                    $causerType === 'internal'
                        ? "{$causerName} created a {$targetType} account for {$targetName}."
                        : "{$targetName} registered a new account.",

                    'updated' =>
                    $causerType === 'internal'
                        ? "{$causerName} updated {$targetName}'s {$targetType} profile."
                        : "{$targetName} updated their profile information.",

                    'deleted' =>
                    $causerType === 'internal'
                        ? "{$causerName} deleted {$targetName}'s {$targetType} account."
                        : "{$targetName} deleted their own account.",

                    'restored' =>
                    "{$causerName} restored {$targetName}'s {$targetType} account.",

                    default =>
                    "{$causerName} performed {$eventName} on {$targetName}.",
                };
            });
    }
}
