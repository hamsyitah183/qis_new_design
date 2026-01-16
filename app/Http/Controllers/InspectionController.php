<?php

namespace App\Http\Controllers;

use App\Models\InspectionApplication;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\country;
use App\Models\PublicCode;

use App\Events\ApplicationCreatedInternalUser;
use App\Events\ApplicationCreatedPublicUser;
use App\Events\InternalUserAdminEvent;
use App\Events\InternalUserClerkEvent;
use App\Events\PublicUserEvent;
use App\Models\ImportPermitLog;
use App\Models\IpCondition;
use App\Models\IpConsignmentAttachment;
use App\Models\IpConsignmentPermit;
use App\Models\PublicUser;
use App\Models\TempAttachment;
use App\Notifications\ApplicationNotification;
use App\Services\ApplicationActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class InspectionController extends Controller
{
    //
    function getInspectionSelf()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.inspection_self', compact('pubmeasure', 'pubpurpose', 'country'));
    }

    function getInspectionOthers()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.inspection_others', compact('pubmeasure', 'pubpurpose', 'country'));
    }

    public function saveApplication(Request $request)
    {
        DB::beginTransaction();

        try {
            // Decode JSON data from frontend
            $exporter = json_decode($request->input('exporterData'), true);
            $importer = json_decode($request->input('importerData'), true);
            $permit   = json_decode($request->input('permitDetails'), true);

            $application = InspectionApplication::create([
                'application_id'       => Str::uuid(),
                'eta'                  => $permit['eta'] ?? null,
                'transport_type'       => $permit['tranType'] ?? null,
                'entry_point'          => $permit['entrypoint'] ?? null,
                'category_application' => $permit['applCate'] ?? null,
                'user_id'              => Auth::user()->uuid,
                'exporter_id'          => $exporter['id'] ?? null,
                'importer_id'          => $importer['uuid'] ?? null,
                'importer_detail'      => $importer ?? [],
                'status'               => 'draft', // or 'submitted', depending on logic
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Application saved successfully',
                'data' => $application
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error saving inspection application: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save application: ' . $e->getMessage()
            ], 500);
        }
    }
}
