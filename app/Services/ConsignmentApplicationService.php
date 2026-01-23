<?php

namespace App\Services;

use App\Models\ConsignmentApplication;
use App\Models\ConsignmentLog;

class ConsignmentApplicationService
{
    public static function log(
        ConsignmentApplication $application,
        string $action,
        ?string $remark = null,
        ?string $status = null,
        $causer = null
    ): ConsignmentLog {
        
        // Use provided causer OR fall back to authUser()
        $auth = authUser(); // your helper

        $causer_id = $causer?->uuid ?? $auth['user']->uuid ?? null;
        $causer_type = $causer
            ? (class_basename($causer) === 'InternalUser' ? 'internal' : 'public')
            : ($auth['type'] ?? 'system');

        return ConsignmentLog::create([
            'application_id' => $application->application_id,
            'causer_id'      => $causer_id,
            'causer_type'    => $causer_type,
            'action'         => $action,
            'remark'         => $remark,
            'status'         => $status,
        ]);
    }
}


