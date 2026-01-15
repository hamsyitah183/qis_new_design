<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Events\InternalUserAdminEvent;
use App\Events\InternalUserClerkEvent;
use App\Events\PublicUserEvent;
use App\Http\Controllers\Controller;
use App\Models\country;
use App\Models\ImportPermitLog;
use App\Models\InternalUser;
use App\Models\IpApplication;
use App\Models\IpCondition;
use App\Models\IpConsignmentAttachment;
use App\Models\IpConsignmentPermit;
use App\Models\PublicCode;
use App\Models\PublicUser;
use App\Models\TempAttachment;
use App\Notifications\ApplicationNotification;
use App\Services\ApplicationActivityLogger;
class InspectionController extends Controller
{
    //
    function getInspection()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.inspection', compact('pubmeasure', 'pubpurpose', 'country'));
    }
}
