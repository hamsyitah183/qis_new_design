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
        $announcements = \App\Models\Announcement::with('releasedBy')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_from')
                      ->orWhere('valid_from', '<=', now()->toDateString());
            })
            ->where(function ($query) {
                $query->whereNull('valid_until')
                      ->orWhere('valid_until', '>=', now()->toDateString());
            })
            ->latest()
            ->get();

        return view('pages.landing', [
            'title' => 'Home',
            'announcements' => $announcements,
        ]);
    }
}