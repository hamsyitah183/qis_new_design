<?php

namespace App\Http\Controllers;

use App\Events\ApplicationDeleted;
use App\Events\PublicUserEvent;
use App\Models\InspectionItem;
use App\Models\InternalUser;
use App\Models\IpConsignmentPermit;
use App\Models\PublicUser;
use App\Notifications\ApplicationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class InspectionPermitController extends Controller
{
    //
  

    public function getView()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        return view('pages.public.inspection.inspection_permit_list', [
            'title' => 'Inspection Certificate Permit List',
        ]);
    }

    public function getAllPermitList()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $userUuid = authUser()['user']->uuid;
        $type = authUser()['type'];

        $query = InspectionItem::query()->with('application'); // eager load application if needed

        // Apply filter for public users
        if ($type !== 'internal') {
            $query->where('public_user_uuid', $userUuid);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()

            ->editColumn('item_name', fn($row) => $row->item_name)
            ->filterColumn('item_name', function ($query, $keyword) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(consignment_detail, '$.item_name'))) LIKE ?", ['%' . strtolower($keyword) . '%']);
            })

            ->addColumn('importer', fn($row) => $row->application->importer->fullname)

            ->addColumn('action', function ($row) use ($type) {
                // Use application_id (correct)
                $viewUrl = '/permit/inspection/' . $row->permit_number;

                $view =
                    '
                <a href="' .
                    $viewUrl .
                    '" class="btn btn-sm btn-primary">
                    <i class="ti ti-eye"></i>
                </a>
            ';

                $downloadPermit = '';

                if ($type === 'internal') {
                    $downloadPermit =
                        '
                    <button 
                        class="btn btn-sm btn-success btn-wave generatePermit ms-2"
                        data-permit="' .
                        $row->id .
                        '">
                        Download Permit
                    </button>
                ';
                }

                return $view . $downloadPermit;
            })

            ->rawColumns(['action'])
            ->make(true);
    }

    function permitDetails($permitNumber)
    {
        return $permitNumber;
    }
}
