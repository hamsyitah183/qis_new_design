<?php

namespace App\Services;

use App\Models\InspectionApplication;
use App\Models\InspectionLog;

class InspectionApplicationService
{
    public static function log(
        InspectionApplication $application,
        string $action,
        ?string $remark = null,
        ?string $status = null,
        $causer = null
    ): InspectionLog {
        
        // Use provided causer OR fall back to authUser()
        $auth = authUser(); // your helper

        $causer_id = $causer?->uuid ?? $auth['user']->uuid ?? null;
        $causer_type = $causer
            ? (class_basename($causer) === 'InternalUser' ? 'internal' : 'public')
            : ($auth['type'] ?? 'system');

        return InspectionLog::create([
            'application_id' => $application->application_id,
            'causer_id'      => $causer_id,
            'causer_type'    => $causer_type,
            'action'         => $action,
            'remark'         => $remark,
            'status'         => $status,
        ]);
    }
}
