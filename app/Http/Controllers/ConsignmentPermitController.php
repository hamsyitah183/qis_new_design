<?php

namespace App\Http\Controllers;

use App\Events\ApplicationDeleted;
use App\Events\PublicUserEvent;
use App\Models\InternalUser;
use App\Models\ConsignmentPermit;
use App\Models\PublicUser;
use App\Notifications\ApplicationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;


class ConsignmentPermitController extends Controller
{
    public function getView()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        return view('pages.public.consignment.consignment_permit_list', [
            'title' => 'Consignment Certificate Permit List',
        ]);
    }

    public function getAllPermitList()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $userUuid = authUser()['user']->uuid;
        $type = authUser()['type'];

        $query = ConsignmentPermit::query()->with('application.user'); // eager load application

        // Apply filter for public users
        if ($type !== 'internal') {
            $query->whereHas('application', function ($q) use ($userUuid) {
                $q->where('user_id', $userUuid);
            });
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()

            ->editColumn('item_name', fn($row) => $row->item_name)
            ->filterColumn('item_name', function ($query, $keyword) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(consignment_detail, '$.item_name'))) LIKE ?", ['%' . strtolower($keyword) . '%']);
            })

            ->addColumn('importer', fn($row) => $row->application->user->fullname ?? '-')

            ->addColumn('action', function ($row) use ($type) {
                // Use application UUID for the view URL to link to consignment details page
                $viewUrl = '/view_consignment/' . $row->application->uuid;

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
