<?php

namespace App\Services;

use App\Models\IpApplication;
use App\Models\ImportPermitLog;

class ApplicationService
{
    public static function log(
        IpApplication $application,
        string $action,
        ?string $remark = null,
        ?string $status = null,
        $causer = null
    ): ImportPermitLog {

        // Use provided causer OR fall back to authUser()
        $auth = authUser(); // your helper

        $causer_id = $causer?->uuid ?? $auth['user']->uuid ?? null;
        $causer_type = $causer
            ? (class_basename($causer) === 'InternalUser' ? 'internal' : 'public')
            : $auth['type']; // fallback to authUser()['type']

        return ImportPermitLog::create([
            'application_id' => $application->application_id,
            'causer_id'      => $causer_id,
            'causer_type'    => $causer_type,
            'action'         => $action,
            'remark'         => $remark,
            'status'         => $status,
        ]);
    }
}

