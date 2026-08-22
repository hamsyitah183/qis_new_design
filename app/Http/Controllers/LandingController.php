<?php

namespace App\Http\Controllers;

use App\Events\ApplicationCreatedInternalUser;
use App\Events\ApplicationCreatedPublicUser;
use App\Events\ApplicationDeleted;
use App\Events\PublicUserEvent;
use App\Models\ConsignmentApplication;
use App\Models\ConsignmentImporter;
use App\Models\Country;
use App\Models\Exporter;
use App\Models\InspectionApplication;
use App\Models\InternalUser;
use App\Models\IpApplication;
use App\Models\IpConsignmentAttachment;
use App\Models\InspectionItem;
use App\Models\IpConsignmentPermit;
use App\Models\ConsignmentPermit;
use App\Models\Gallery;
use App\Models\IpEntryPoint;
use App\Models\Order;
use App\Models\PublicCode;
use App\Models\PublicUser;
use App\Models\QrScanLog;
use App\Notifications\ApplicationNotification;
use App\Services\ApplicationActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class LandingController extends Controller
{
    function landing()
    {
        $announcements = \App\Models\Announcement::all();
        $galleries = Gallery::all();
        $districts = PublicCode::where('cate_name', 'district_entry')
            ->pluck('description', 'cate_code');

        // Get one representative entry point per district
        $entryPoints = IpEntryPoint::select('district', 'entry_name', 'transport_type')
            ->where('is_del', false)
            ->orderBy('district')
            ->orderBy('id')
            ->get()
            ->groupBy('district')
            ->map(function ($group) use ($districts) {
                $first = $group->first();
                return [
                    'district_id' => $first->district,
                    'district_name' => $districts[$first->district] ?? 'District ' . $first->district,
                    'entry_name' => $first->entry_name,
                    'transport_type' => $first->transport_type,
                ];
            })
            ->values();

        return view('pages.landing', [
            'title' => 'Home',
            'announcements' => $announcements,
            'galleries' => $galleries,
            'entryPoints' => $entryPoints
        ]);
    }
}
