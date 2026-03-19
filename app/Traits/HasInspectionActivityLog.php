<?php

namespace App\Traits;

use App\Services\InspectionApplicationService;
use Illuminate\Support\Facades\Auth;

trait HasInspectionActivityLog
{
    /**
     * Log an activity for this inspection application.
     * 
     * @param string $action
     * @param string|null $remark
     * @param string|null $status
     * @param mixed|null $causer optional, defaults to authenticated user
     * @return \App\Models\InspectionLog
     */
    public function logActivity(string $action, ?string $remark = null, ?string $status = null, $causer = null)
    {
        $causer = $causer ?? Auth::user();
        
        // Determine causer type
        $type = null;
        if ($causer) {
            $type = class_basename($causer) === 'InternalUser' ? 'internal' : 'public';
        }

        return InspectionApplicationService::log(
            $this,      // application
            $action,    // action
            $remark,    // remark
            $status,    // status
            $causer     // causer
        );
    }
}





