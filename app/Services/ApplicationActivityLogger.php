<?php

namespace App\Services;

use Spatie\Activitylog\Models\Activity;
use App\Models\IpApplication;

class ApplicationActivityLogger
{
    public static function log(
        IpApplication $application,
        string $event,
        string $description,
        array $properties = [],
        string $id = null
    ): void {
        $auth = authUser();

        activity()
            ->useLog('application_activity')
            ->event($event)
            ->performedOn($application)
            ->causedBy($auth['user'] ?? $id ?? null)
            ->withProperties(array_merge([
                'application_id' => $application->application_id,
                'causer_type' => $auth['type'] ?? 'system',
                'status' => $application->status,
            ], $properties))
            ->log($description);
    }
}
