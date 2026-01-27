<?php

namespace App\Http\Controllers;

use App\Charts\ApplicationHorizontalChart;
use App\Charts\LineUserChart;
use App\Charts\MonthlyUsersChart;
use App\Charts\OrderDonutChart;
use App\Charts\PaymentMethodBarChart;
// use App\Charts\ApplicationHorizontalChart;
use App\Models\IpApplication;
use App\Models\InspectionApplication;
use App\Models\ConsignmentApplication;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Charts\ClerkApplicationStatusChart;
use App\Charts\ClerkDailyWorkloadChart;
use App\Charts\ClerkDailyVolumeChart;
use App\Charts\PublicApplicationStatusChart;
use App\Models\Country;
use App\Models\IpEntryPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\DatabaseNotification;


// use Illuminate\Support\Facades\Notification;

class DashboardController extends Controller
{
    //
    public function dashboard(LineUserChart $lineChart,  OrderDonutChart $orderChart, PaymentMethodBarChart $paymentChart, ApplicationHorizontalChart $applicationChart)
    {
        // ✅ Check which guard is logged in
        if (Auth::guard('public')->check()) {
            return $this->public_dashboard(
                app(PublicApplicationStatusChart::class)
            );
        }

        if (Auth::guard('internal')->check()) {
            return $this->internal_dashboard(
                app(LineUserChart::class),
                app(OrderDonutChart::class),
                app(PaymentMethodBarChart::class),
                app(ApplicationHorizontalChart::class),
                app(ClerkApplicationStatusChart::class),
                app(ClerkDailyWorkloadChart::class),
                app(ClerkDailyVolumeChart::class)
            );
            return $this->internal_dashboard($lineChart, $orderChart, $paymentChart, $applicationChart);
        }

        // ❌ If no guard is logged in, redirect to login
        return redirect()->route('login');
    }

    protected function public_dashboard(
        PublicApplicationStatusChart $statusChart
    ) {
        $userId = Auth::id();

        // KPI Counts
        $draftCount = IpApplication::where('status', '=', 'Draft')->where('user_id', '=', $userId)->count() +
                      InspectionApplication::where('status', '=', 'Draft')->where('user_id', '=', $userId)->count() +
                      ConsignmentApplication::where('status', '=', 'Draft')->where('user_id', '=', $userId)->count();

        $pendingCount = IpApplication::whereIn('status', ['Clerk Review In-Progress', 'Awaiting Approval'])->where('user_id', '=', $userId)->count() +
                        InspectionApplication::whereIn('status', ['Clerk review in-progress', 'wait for company approval'])->where('user_id', '=', $userId)->count() +
                        ConsignmentApplication::whereIn('status', ['Clerk Review In-Progress', 'wait for company approval'])->where('user_id', '=', $userId)->count();

        $verifiedCount = IpApplication::where('status', '=', 'Clerk Verified')->where('user_id', '=', $userId)->count() +
                         InspectionApplication::where('status', '=', 'Clerk Verified')->where('user_id', '=', $userId)->count() +
                         ConsignmentApplication::where('status', '=', 'Clerk Verified')->where('user_id', '=', $userId)->count();

        $rejectedCount = IpApplication::where('status', '=', 'Clerk Rejected')->where('user_id', '=', $userId)->count() +
                         InspectionApplication::where('status', '=', 'Clerk Rejected')->where('user_id', '=', $userId)->count() +
                         ConsignmentApplication::where('status', '=', 'Clerk Rejected')->where('user_id', '=', $userId)->count();

        // Recent Applications
        $recentIp = IpApplication::where('user_id', '=', $userId)->latest()->take(5)->get()->map(function($item) {
            $item->type = 'Import Permit';
            return $item;
        });
        $recentInspection = InspectionApplication::where('user_id', '=', $userId)->latest()->take(5)->get()->map(function($item) {
            $item->type = 'Inspection';
            return $item;
        });
        $recentConsignment = ConsignmentApplication::where('user_id', '=', $userId)->latest()->take(5)->get()->map(function($item) {
            $item->type = 'Consignment';
            return $item;
        });

        $recentApplications = $recentIp->concat($recentInspection)->concat($recentConsignment)
            ->sortByDesc('created_at')
            ->take(5);

        return view('dashboard.public.public_dashboard', [
            'draftCount' => $draftCount,
            'pendingCount' => $pendingCount,
            'verifiedCount' => $verifiedCount,
            'rejectedCount' => $rejectedCount,
            'statusChart' => $statusChart->build(),
            'recentApplications' => $recentApplications,
        ]);
    }

    protected function internal_dashboard(
        LineUserChart $lineChart, 
        OrderDonutChart $orderChart, 
        PaymentMethodBarChart $paymentChart, 
        ApplicationHorizontalChart $applicationChart,
        ClerkApplicationStatusChart $clerkStatusChart,
        ClerkDailyWorkloadChart $clerkWorkloadChart,
        ClerkDailyVolumeChart $clerkVolumeChart
    ) {
        $role = authUser()['roles'][0];

        $data = [
            'userLineChart' => $lineChart->build(),
            'orderChart' => $orderChart->build(),
            'paymentChart' => $paymentChart->build(),
            'applicationChart' => $applicationChart->build(),
            'clerkVolumeChart' => $clerkVolumeChart->build()
        ];

        if ($role === 'clerk') {
            // KPI Counts
            $data['pendingPermits'] = IpApplication::where('status', '=', 'Clerk Review In-Progress')->count();
            $data['pendingInspections'] = InspectionApplication::where('status', '=', 'Clerk review in-progress')->count();
            $data['pendingConsignments'] = ConsignmentApplication::where('status', '=', 'Clerk Review In-Progress')->count();
            
            // Verified Today (example logic: applications updated to a "verified" status today)
            $today = Carbon::today();
            $verifiedTodayPermits = IpApplication::where('status', '=', 'Clerk Verified')
                ->whereDate('updated_at', '=', $today)->count();
            $verifiedTodayInspections = InspectionApplication::where('status', '=', 'Clerk Verified')
                ->whereDate('updated_at', '=', $today)->count();
            $verifiedTodayConsignments = ConsignmentApplication::where('status', '=', 'Clerk Verified')
                ->whereDate('updated_at', '=', $today)->count();
            
            $data['verifiedToday'] = $verifiedTodayPermits + $verifiedTodayInspections + $verifiedTodayConsignments;

            // Charts
            $data['clerkStatusChart'] = $clerkStatusChart->build();
            $data['clerkWorkloadChart'] = $clerkWorkloadChart->build();
            $data['clerkVolumeChart'] = $clerkVolumeChart->build();

            // Action Needed Queue (Oldest 5 pending applications)
            $pendingApps = collect();
            
            $permits = IpApplication::with('user')->where('status', '=', 'Clerk Review In-Progress')
                ->orderBy('created_at', 'asc')->take(5)->get()->map(function($item) {
                    $item->type = 'Import Permit';
                    return $item;
                });
            
            $inspections = InspectionApplication::with('user')->where('status', '=', 'Clerk review in-progress')
                ->orderBy('created_at', 'asc')->take(5)->get()->map(function($item) {
                    $item->type = 'Inspection';
                    return $item;
                });
                
            $consignments = ConsignmentApplication::with('user')->where('status', '=', 'Clerk Review In-Progress')
                ->orderBy('created_at', 'asc')->take(5)->get()->map(function($item) {
                    $item->type = 'Consignment';
                    return $item;
                });

            $data['pendingQueue'] = $pendingApps->concat($permits)->concat($inspections)->concat($consignments)
                ->sortBy('created_at')->take(5);
        }

        return view('dashboard.internal.main_dashboard', $data);
    }

    public function get_country($code)
    {
        $country = Country::where('code', $code)->first();

        return response()->json($country);
    }

    public function get_entry_point($id)
    {
        $entry = IpEntryPoint::find($id);

        return response()->json($entry);
    }

    public function get_notifications()
    {
        $type = authUser()['type'];
        $notifications = DatabaseNotification::where('notifiable_type', $type)
            ->where('notifiable_id', authUser()['user']->uuid)
            ->latest()
            ->take(10)
            ->get();

        return response()->json($notifications);
    }

    public function notifications_page()
    {
        return view('pages.notifications');
    }

    public function notifications_data(Request $request)
    {
        $type = authUser()['type'];
        $user = authUser()['user'];

        $query = DatabaseNotification::where('notifiable_type', $type)
            ->where('notifiable_id', $user->uuid);

        if ($request->filled('hours')) {
            $query->where(
                'created_at',
                '>=',
                Carbon::now()->subHours($request->hours)
            );
        }

        return response()->json(
            $query->latest()->get()
        );
    }
}
