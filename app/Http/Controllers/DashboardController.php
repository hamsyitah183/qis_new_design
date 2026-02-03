<?php

namespace App\Http\Controllers;

use App\Charts\ApplicationHorizontalChart;
use App\Charts\LineUserChart;
use App\Charts\MonthlyUsersChart;
use App\Charts\OrderDonutChart;
use App\Charts\PaymentMethodBarChart;
// use App\Charts\ApplicationHorizontalChart;
// use App\Charts\ApplicationHorizontalChart;
use App\Models\IpApplication;
use App\Models\InspectionApplication;
use App\Models\ConsignmentApplication;
// use App\Models\ClerkDailyVolumeChart;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Charts\ClerkApplicationStatusChart;
use App\Charts\ClerkDailyWorkloadChart;
use App\Charts\ClerkDailyVolumeChart;
use App\Charts\PublicApplicationStatusChart;
use App\Charts\PermitDailyProcessChart;
use App\Models\Country;
use App\Models\IpEntryPoint;
use App\Models\Order;
use App\Models\IpConsignmentPermit;
use App\Models\InspectionItem;
use App\Models\ConsignmentPermit;
use App\Models\InternalUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Notifications\DatabaseNotification;

// use Illuminate\Support\Facades\Notification;

class DashboardController extends Controller
{
    //
    public function dashboard(LineUserChart $lineChart, OrderDonutChart $orderChart, PaymentMethodBarChart $paymentChart, ApplicationHorizontalChart $applicationChart, ClerkDailyVolumeChart $clerkVolumeChart, PermitDailyProcessChart $permitChart, ClerkApplicationStatusChart $clerkStatusChart, ClerkDailyWorkloadChart $clerkWorkloadChart)
    {
        // ✅ Check which guard is logged in
        if (Auth::guard('public')->check()) {
            return $this->public_dashboard(app(PublicApplicationStatusChart::class));
        }

        if (Auth::guard('internal')->check()) {
            if (authUser()['user']->hasRole('finance')) {
                return view('dashboard.internal.finance_dashboard');
            }
            return $this->internal_dashboard($lineChart, $orderChart, $paymentChart, $applicationChart, $clerkStatusChart, $clerkWorkloadChart, $clerkVolumeChart, $permitChart);
        }

        // ❌ If no guard is logged in, redirect to login
        return redirect()->route('login');
    }

    protected function public_dashboard(PublicApplicationStatusChart $statusChart)
    {
        $userId = Auth::id();

        // KPI Counts
        $draftCount = IpApplication::where('status', '=', 'Draft')->where('user_id', '=', $userId)->count() + InspectionApplication::where('status', '=', 'Draft')->where('user_id', '=', $userId)->count() + ConsignmentApplication::where('status', '=', 'Draft')->where('user_id', '=', $userId)->count();

        $pendingCount =
            IpApplication::whereIn('status', ['Clerk Review In-Progress', 'Awaiting Approval'])
                ->where('user_id', '=', $userId)
                ->count() +
            InspectionApplication::whereIn('status', ['Clerk review in-progress', 'wait for company approval'])
                ->where('user_id', '=', $userId)
                ->count() +
            ConsignmentApplication::whereIn('status', ['Clerk Review In-Progress', 'wait for company approval'])
                ->where('user_id', '=', $userId)
                ->count();

        $verifiedCount = IpApplication::where('status', '=', 'Clerk Verified')->where('user_id', '=', $userId)->count() + InspectionApplication::where('status', '=', 'Clerk Verified')->where('user_id', '=', $userId)->count() + ConsignmentApplication::where('status', '=', 'Clerk Verified')->where('user_id', '=', $userId)->count();

        $rejectedCount = IpApplication::where('status', 'like', '%Rejected%')->where('user_id', '=', $userId)->count() + InspectionApplication::where('status', 'like', '%Rejected%')->where('user_id', '=', $userId)->count() + ConsignmentApplication::where('status', 'like', '%Rejected%')->where('user_id', '=', $userId)->count();

        $pendingPaymentCount =
            IpApplication::where('user_id', $userId)
                ->whereHas('consignmentPermits', function ($q) {
                    $q->where('status', 'pending for payment');
                })
                ->count() +
            InspectionApplication::where('user_id', $userId)
                ->whereHas('inspectionItems', function ($q) {
                    $q->where('status', 'pending for payment');
                })
                ->count() +
            ConsignmentApplication::where('user_id', $userId)
                ->whereHas('consignmentPermits', function ($q) {
                    $q->where('status', 'pending for payment');
                })
                ->count();
        $rejectedCount = IpApplication::where('status', '=', 'Clerk Rejected')->where('user_id', '=', $userId)->count() + InspectionApplication::where('status', '=', 'Clerk Rejected')->where('user_id', '=', $userId)->count() + ConsignmentApplication::where('status', '=', 'Clerk Rejected')->where('user_id', '=', $userId)->count();

        // Recent Applications
        $recentIp = IpApplication::where('user_id', '=', $userId)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                $item->type = 'Import Permit';
                return $item;
            });
        $recentInspection = InspectionApplication::where('user_id', '=', $userId)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                $item->type = 'Inspection';
                return $item;
            });
        $recentConsignment = ConsignmentApplication::where('user_id', '=', $userId)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                $item->type = 'Consignment';
                return $item;
            });

        $recentApplications = $recentIp->concat($recentInspection)->concat($recentConsignment)->sortByDesc('created_at')->take(5);

        return view('dashboard.public.public_dashboard', [
            'draftCount' => $draftCount,
            'pendingCount' => $pendingCount,
            'verifiedCount' => $verifiedCount,
            'rejectedCount' => $rejectedCount,
            'pendingPaymentCount' => $pendingPaymentCount,
            'statusChart' => $statusChart->build(),
            'recentApplications' => $recentApplications,
        ]);
    }

    public function internal_dashboard(LineUserChart $lineChart, OrderDonutChart $orderChart, PaymentMethodBarChart $paymentChart, ApplicationHorizontalChart $applicationChart, ClerkApplicationStatusChart $clerkStatusChart, ClerkDailyWorkloadChart $clerkWorkloadChart, ClerkDailyVolumeChart $clerkVolumeChart, PermitDailyProcessChart $permitChart)
    {
        // $notifications = Notification::where('notifiable_type', 'internal')
        //     ->where('notifiable_id', authUser()['user']->uuid)
        //     ->latest()
        //     ->take(10)
        //     ->get();

        // Fetch latest applications from all three types
        $importPermits = \App\Models\IpApplication::with('user')
            ->where('status', 'Completed')
            ->get();
           
        $inspectionCerts = \App\Models\InspectionApplication::with('user')
            ->where('status', 'Completed')
            ->get();
           

        $consignmentCerts = \App\Models\ConsignmentApplication::with('user')
            ->where('status', 'Completed')
            ->get();
           

        // Combine and sort all applications
        if(authUser()['roles'][0] == 'boundary officer') {
            $boundary = InternalUser::with(['boundaryOfficer.entryPoint'])->where('uuid', authUser()['user']['uuid'])->first();
            // dd($boundary->boundaryOfficer);
            if (authUser()['roles'][0] === 'boundary officer') {

                $boundary = InternalUser::with('boundaryOfficer')
                    ->where('uuid', authUser()['user']['uuid'])
                    ->first();
            
                $entryPoint = $boundary?->boundaryOfficer?->ip_entry_id;

                // dd( $importPermits, $entryPoint);
            
                $latestApplications = $importPermits
                    ->concat($inspectionCerts)
                    ->concat($consignmentCerts)
                    ->filter(fn ($app) => $app->entry_point === $entryPoint)
                    ->sortByDesc('created_at')
                    ->take(5)
                    ->values();
            } 
            

        }else {
            $latestApplications =  $importPermits
            ->concat($inspectionCerts)
            ->concat($consignmentCerts)
            ->sortByDesc('created_at')
            ->take(5)
            ->values();
        }
        

        // Get statistics counts
        $totalImportPermits = \App\Models\IpApplication::count();
        $totalInspectionCerts = \App\Models\InspectionApplication::count();
        $totalConsignmentCerts = \App\Models\ConsignmentApplication::count();

        // Count all approved/accepted applications across all types
        $totalAccepted = \App\Models\IpApplication::where('status', 'Approved')->count() + \App\Models\InspectionApplication::where('status', 'Approved')->count() + \App\Models\ConsignmentApplication::where('status', 'Approved')->count();

        // Get recent activity logs
        try {
            $recentActivities = Activity::with('causer')->latest()->take(10)->get();
        } catch (\Exception $e) {
            // If activity log fails, just show empty array
            $recentActivities = collect([]);
        }

        $role = authUser()['roles'][0];

        // KPI Counts
        $data['pendingPermits'] = IpApplication::where('status', '=', 'Clerk Review In-Progress')->count();
        $data['pendingInspections'] = InspectionApplication::where('status', '=', 'Clerk review in-progress')->count();
        $data['pendingConsignments'] = ConsignmentApplication::where('status', '=', 'Clerk Review In-Progress')->count();

        // Verified Today (example logic: applications updated to a "verified" status today)
        $today = Carbon::today();
        $verifiedTodayPermits = IpApplication::where('status', '=', 'Clerk Verified')->whereDate('updated_at', '=', $today)->count();
        $verifiedTodayInspections = InspectionApplication::where('status', '=', 'Clerk Verified')->whereDate('updated_at', '=', $today)->count();
        $verifiedTodayConsignments = ConsignmentApplication::where('status', '=', 'Clerk Verified')->whereDate('updated_at', '=', $today)->count();

        $data['verifiedToday'] = $verifiedTodayPermits + $verifiedTodayInspections + $verifiedTodayConsignments;

        // Charts
        $data['clerkStatusChart'] = $clerkStatusChart->build();
        $data['clerkWorkloadChart'] = $clerkWorkloadChart->build();
        $data['clerkVolumeChart'] = $clerkVolumeChart->build();

        // Action Needed Queue (Oldest 5 pending applications)
        $pendingApps = collect();

        $permits = IpApplication::with('user')
            ->where('status', '=', 'Clerk Review In-Progress')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $item->type = 'Import Permit';
                return $item;
            });

        $inspections = InspectionApplication::with('user')
            ->where('status', '=', 'Clerk review in-progress')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $item->type = 'Inspection';
                return $item;
            });

        $consignments = ConsignmentApplication::with('user')
            ->where('status', '=', 'Clerk Review In-Progress')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $item->type = 'Consignment';
                return $item;
            });

        $data['pendingQueue'] = $pendingApps->concat($permits)->concat($inspections)->concat($consignments)->sortBy('created_at')->take(5);

        $totalPayment = Order::where('status', 'payment complete')->sum('payment_amount');

        return view('dashboard.internal.main_dashboard', [
            // 'notifications' => $notifications,
            'userLineChart' => $lineChart->build(),
            'orderChart' => $orderChart->build(),
            'paymentChart' => $paymentChart->build(),
            'applicationChart' => $applicationChart->build(),
            'latestApplications' => $latestApplications,
            'totalImportPermits' => $totalImportPermits,
            'totalInspectionCerts' => $totalInspectionCerts,
            'totalConsignmentCerts' => $totalConsignmentCerts,
            'totalAccepted' => $totalAccepted,
            'recentActivities' => $recentActivities,
            'clerkVolumeChart' => $data['clerkVolumeChart'],
            'clerkStatusChart' => $data['clerkStatusChart'],
            'clerkWorkloadChart' => $data['clerkWorkloadChart'],
            'pendingQueue' => $data['pendingQueue'],
            'verifiedToday' => $data['verifiedToday'],
            'permitChart' => $permitChart->build(),
        ]); // Internal user dashboard
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

        $query = DatabaseNotification::where('notifiable_type', $type)->where('notifiable_id', $user->uuid);

        if ($request->filled('hours')) {
            $query->where('created_at', '>=', Carbon::now()->subHours($request->hours));
        }

        return response()->json($query->latest()->get());
    }

    public function applicationCount()
    {
        $auth = authUser();

        if (!$auth) {
            return response()->json(['data' => []], 401);
        }

        if ($auth['roles']->contains('admin') || $auth['roles']->contains('superadmin')) {
            $data = $this->adminCount();
        } elseif ($auth['roles']->contains('clerk')) {
            return $this->clerkCount();
        } elseif ($auth['roles']->contains('officer')) {
            return $this->officerCount();
        } else {
            $data = [];
        }

        return response()->json([
            'data' => $data,
        ]);
    }


    private function adminCount()
    {
        // In-progress
        $data['ipCount'] = IpApplication::count();
        $data['icCount'] = InspectionApplication::count();
        $data['ccCount'] = ConsignmentApplication::count();
    
        // Verified
        $ipVerified = IpApplication::where('status', 'Clerk Approved')->count();
        $icVerified = InspectionApplication::where('status', 'Clerk Approved')->count();
        $ccVerified = ConsignmentApplication::where('status', 'Clerk Approved')->count();
    
        // ✅ FIX: numeric sum
        $data['verified'] = $ipVerified + $icVerified + $ccVerified;
    
        // ✅ Revenue (successful payments only)
        $data['total'] = Order::where('transaction_status', 'SUCCESSFUL')
            ->sum('payment_amount');
    
        // Officers
        $data['ipOfficer'] = IpConsignmentPermit::count();
        $data['icOfficer'] = InspectionItem::count();
        $data['ccOfficer'] = ConsignmentPermit::count();
    
        $data['officer'] = $data['ipOfficer'] + $data['icOfficer'] + $data['ccOfficer'];
    
        // Review officer
        $data['totalReview'] = $data['verified'];
    
        return $data;
    }
    

    public function clerkCount()
    {
        $ipCount = IpApplication::where('status', 'Clerk Review In-Progress')->count();
        $icCount = InspectionApplication::where('status', 'Clerk Review In-Progress')->count();
        $ccCount = ConsignmentApplication::where('status', 'Clerk Review In-Progress')->count();
        $data['ipCount'] = $ipCount;
        $data['icCount'] = $icCount;
        $data['ccCount'] = $ccCount;

        $ipVerified = IpApplication::where('status', 'Clerk Approved')->count();
        $icVerified = InspectionApplication::where('status', 'Clerk Approved')->count();
        $ccVerified = ConsignmentApplication::where('status', 'Clerk Approved')->count();

        $totalVerified = $ipVerified . $icVerified . $ccVerified;

        $totalAmount = Order::where('transaction_status', 'SUCCESSFUL')->sum('payment_amount');

        $data['total'] = $totalAmount;
        $data['verified'] = $totalVerified;

        return response()->json([
            'data' => $data,
        ]);
    }
    public function officerCount()
    {
        $ipCountOfficer = IpConsignmentPermit::count();
        $icCountOfficer = InspectionItem::count();
        $ccCountOfficer = ConsignmentPermit::count();
        $data['ipOfficer'] = $ipCountOfficer;
        $data['icOfficer'] = $icCountOfficer;
        $data['ccOfficer'] = $ccCountOfficer;

        $ipVerified = IpApplication::where('status', 'Clerk Approved')->count();
        $icVerified = InspectionApplication::where('status', 'Clerk Approved')->count();
        $ccVerified = ConsignmentApplication::where('status', 'Clerk Approved')->count();

        $totalOfficer = $ipCountOfficer . $icCountOfficer . $ccCountOfficer;

        $totalAmount = Order::where('transaction_status', 'SUCCESSFUL')->sum('payment_amount');

        $data['total'] = $totalAmount;
        // $data['verified'] = $totalVerified;

        return response()->json([
            'data' => $data,
        ]);
    }


}
