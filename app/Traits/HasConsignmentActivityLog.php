<?php

namespace App\Traits;

// use App\Services\ApplicationActivityLogger;
use App\Services\ConsignmentApplicationService;
use Illuminate\Support\Facades\Auth;

trait HasConsignmentActivityLog
{
    /**
     * Log an activity for this application
     *
     * @param string $action
     * @param string|null $remark
     * @param string|null $status
     * @param mixed|null $causer optional, defaults to authenticated user
     * @return \App\Models\ImportPermitLog
     */
    public function logActivity(string $action, ?string $remark = null, ?string $status = null, $causer = null)
    {
        $causer = $causer ?? Auth::user();

        // Determine causer type
        $type = null;
        if ($causer) {
            $type = class_basename($causer) === 'InternalUser' ? 'internal' : 'public';
        }

        return ConsignmentApplicationService::log(
            $this,      // application
            $action,    // action
            $remark,    // remark
            $status,    // status
            $causer     // causer
        );
    }
}
