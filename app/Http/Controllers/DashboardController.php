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
use App\Models\Announcement;
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
use Illuminate\Support\Facades\Artisan;

// use Illuminate\Support\Facades\Notification;

class DashboardController extends Controller
{
    //
    public function dashboard(LineUserChart $lineChart, OrderDonutChart $orderChart, PaymentMethodBarChart $paymentChart, ApplicationHorizontalChart $applicationChart, ClerkDailyVolumeChart $clerkVolumeChart, PermitDailyProcessChart $permitChart, ClerkApplicationStatusChart $clerkStatusChart, ClerkDailyWorkloadChart $clerkWorkloadChart)
    {
        Artisan::call('bayupay:check-pending');
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

        // Data for Bar Chart: Applications Received (Last 5 Months)
        $barCategories = [];
        $barData = [];
        for ($i = 4; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $barCategories[] = $month->format('M Y');

            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $count =
                IpApplication::where('user_id', $userId)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count() +
                InspectionApplication::where('user_id', $userId)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count() +
                ConsignmentApplication::where('user_id', $userId)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

            $barData[] = $count;
        }

        // Data for Line Chart: Application Trends (Current Week Mon-Sun)
        $lineCategories = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $lineData = [0, 0, 0, 0, 0, 0, 0];
        $startOfWeek = now()->startOfWeek(); // Monday
        $endOfWeek = now()->endOfWeek(); // Sunday

        $ipApps = IpApplication::where('user_id', $userId)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();
        $insApps = InspectionApplication::where('user_id', $userId)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();
        $consApps = ConsignmentApplication::where('user_id', $userId)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();

        $allAppsThisWeek = $ipApps->concat($insApps)->concat($consApps);

        foreach ($allAppsThisWeek as $app) {
            $dayIndex = $app->created_at->isoWeekday() - 1; // 1 (Mon) to 7 (Sun) -> 0 to 6
            $lineData[$dayIndex]++;
        }

        return view('dashboard.public.public_dashboard', [
            'draftCount' => $draftCount,
            'pendingCount' => $pendingCount,
            'verifiedCount' => $verifiedCount,
            'rejectedCount' => $rejectedCount,
            'pendingPaymentCount' => $pendingPaymentCount,
            'statusChart' => $statusChart->build(),
            'recentApplications' => $recentApplications,
            'barCategories' => $barCategories,
            'barData' => $barData,
            'lineCategories' => $lineCategories,
            'lineData' => $lineData,
        ]);
    }

    public function internal_dashboard(
        LineUserChart $lineChart,
        OrderDonutChart $orderChart,
        PaymentMethodBarChart $paymentChart,
        ApplicationHorizontalChart $applicationChart,
        ClerkApplicationStatusChart $clerkStatusChart,
        ClerkDailyWorkloadChart $clerkWorkloadChart,
        ClerkDailyVolumeChart $clerkVolumeChart,
        PermitDailyProcessChart $permitChart
    ) {
        // ─── 1. Handle boundary officer separately ──────────────────────────
        if (authUser()['roles'][0] === 'boundary officer') {
            return $this->boundaryOfficerDashboard();
        }

        // ─── 2. Common data for other internal roles ────────────────────────
        $completedImportPermits = IpApplication::with('user')->where('status', 'Completed')->get();
        $completedInspections = InspectionApplication::with('user')->where('status', 'Completed')->get();
        $completedConsignments = ConsignmentApplication::with('user')->where('status', 'Completed')->get();

        $latestApplications = $completedImportPermits
            ->concat($completedInspections)
            ->concat($completedConsignments)
            ->sortByDesc('created_at')
            ->take(5)
            ->values();

        $totalImportPermits = IpApplication::count();
        $totalInspectionCerts = InspectionApplication::count();
        $totalConsignmentCerts = ConsignmentApplication::count();

        $totalAccepted = $this->getTotalAcceptedApplications();

        // ─── 3. Recent activity logs ─────────────────────────────────────────
        $recentActivities = $this->getRecentActivities();

        // ─── 4. KPI counts ───────────────────────────────────────────────────
        $kpi = $this->getKpiCounts();

        // ─── 5. Charts ───────────────────────────────────────────────────────
        $charts = [
            'lineChart'          => $lineChart->build(),
            'orderChart'         => $orderChart->build(),
            'paymentChart'       => $paymentChart->build(),
            'applicationChart'   => $applicationChart->build(),
            'clerkStatusChart'   => $clerkStatusChart->build(),
            'clerkWorkloadChart' => $clerkWorkloadChart->build(),
            'clerkVolumeChart'   => $clerkVolumeChart->build(),
            'permitChart'        => $permitChart->build(),
        ];

        // ─── 6. Pending queue with overdue flag ─────────────────────────────
        $pendingQueue = $this->getPendingQueue();

        // ─── 7. Total payment ───────────────────────────────────────────────
        $totalPayment = Order::where('status', 'payment complete')->sum('payment_amount');

        // ─── 8. Announcements ───────────────────────────────────────────────
        $announcements = $this->getAnnouncements();

        // ─── 9. Overdue pending applications ────────────────────────────────
        $overduePendingApps = $this->getOverduePendingAppsCount();

        // ─── 10. Return view ────────────────────────────────────────────────
        return view('dashboard.internal.main_dashboard', [
            // Charts
            'userLineChart'      => $charts['lineChart'],
            'orderChart'         => $charts['orderChart'],
            'paymentChart'       => $charts['paymentChart'],
            'applicationChart'   => $charts['applicationChart'],
            'clerkStatusChart'   => $charts['clerkStatusChart'],
            'clerkWorkloadChart' => $charts['clerkWorkloadChart'],
            'clerkVolumeChart'   => $charts['clerkVolumeChart'],
            'permitChart'        => $charts['permitChart'],

            // Completed applications
            'latestApplications' => $latestApplications,

            // Totals
            'totalImportPermits'     => $totalImportPermits,
            'totalInspectionCerts'   => $totalInspectionCerts,
            'totalConsignmentCerts'  => $totalConsignmentCerts,
            'totalAccepted'          => $totalAccepted,

            // KPI
            'pendingPermits'         => $kpi['pendingPermits'],
            'pendingInspections'     => $kpi['pendingInspections'],
            'pendingConsignments'    => $kpi['pendingConsignments'],
            'verifiedToday'          => $kpi['verifiedToday'],

            // Activity & queue
            'recentActivities'       => $recentActivities,
            'pendingQueue'           => $pendingQueue,

            // Payments & announcements
            'totalPayment'           => $totalPayment,
            'announcements'          => $announcements,

            // Overdue
            'overduePendingApps'     => $overduePendingApps,
        ]);
    }

// ─── Private helper methods ──────────────────────────────────────────

    /**
     * Dashboard for boundary officers – filtered by entry point.
     */
    private function boundaryOfficerDashboard()
    {
        $boundary = InternalUser::with(['boundaryOfficer.entryPoint'])
            ->where('uuid', authUser()['user']['uuid'])
            ->first();

        $entryPoint = $boundary?->boundaryOfficer?->ip_entry_id;

        // Fetch completed applications and filter by entry point
        $importPermits = IpApplication::with('user')->where('status', 'Completed')->get();
        $inspectionCerts = InspectionApplication::with('user')->where('status', 'Completed')->get();
        $consignmentCerts = ConsignmentApplication::with('user')->where('status', 'Completed')->get();

        if ($entryPoint) {
            $importPermits = $importPermits->filter(fn($app) => $app->entry_point == $entryPoint)->sortByDesc('created_at')->values();
            $inspectionCerts = $inspectionCerts->filter(fn($app) => $app->entry_point == $entryPoint)->sortByDesc('created_at')->values();
            $consignmentCerts = $consignmentCerts->filter(fn($app) => $app->entry_point == $entryPoint)->sortByDesc('created_at')->values();
        } else {
            $importPermits = $importPermits->sortByDesc('created_at')->values();
            $inspectionCerts = $inspectionCerts->sortByDesc('created_at')->values();
            $consignmentCerts = $consignmentCerts->sortByDesc('created_at')->values();
        }

        $totalImportPermits = $importPermits->count();
        $totalInspectionCerts = $inspectionCerts->count();
        $totalConsignmentCerts = $consignmentCerts->count();

        $announcements = $this->getAnnouncements();
        $overduePendingApps = $this->getOverduePendingAppsCount();

        return view('dashboard.internal.main_dashboard', [
            'latestApplications' => collect(), // not used for boundary officers
            'importPermits'      => $importPermits,
            'inspectionCerts'    => $inspectionCerts,
            'consignmentCerts'   => $consignmentCerts,
            'totalImportPermits' => $totalImportPermits,
            'totalInspectionCerts' => $totalInspectionCerts,
            'totalConsignmentCerts' => $totalConsignmentCerts,
            'announcements'      => $announcements,
            'overduePendingApps' => $overduePendingApps,
        ]);
    }

    /**
     * Get total accepted applications across all types.
     */
    private function getTotalAcceptedApplications(): int
    {
        return IpApplication::where('status', 'Approved')->count()
            + InspectionApplication::where('status', 'Approved')->count()
            + ConsignmentApplication::where('status', 'Approved')->count();
    }

    /**
     * Get recent activity logs (fallback to empty collection).
     */
    private function getRecentActivities()
    {
        try {
            return Activity::with('causer')->latest()->take(10)->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get KPI counts: pending and verified today.
     */
    private function getKpiCounts(): array
    {
        $today = Carbon::today();

        $pendingPermits = IpApplication::where('status', 'Clerk Review In-Progress')->count();
        $pendingInspections = InspectionApplication::where('status', 'Clerk review in-progress')->count();
        $pendingConsignments = ConsignmentApplication::where('status', 'Clerk Review In-Progress')->count();

        $verifiedToday = IpApplication::where('status', 'Clerk Verified')->whereDate('updated_at', $today)->count()
            + InspectionApplication::where('status', 'Clerk Verified')->whereDate('updated_at', $today)->count()
            + ConsignmentApplication::where('status', 'Clerk Verified')->whereDate('updated_at', $today)->count();

        return [
            'pendingPermits'     => $pendingPermits,
            'pendingInspections' => $pendingInspections,
            'pendingConsignments' => $pendingConsignments,
            'verifiedToday'      => $verifiedToday,
        ];
    }

    /**
     * Get pending applications (top 5) from each type, enriched with 'type' and 'is_overdue'.
     */
    private function getPendingQueue()
    {
        $pending = collect();

        $pending = $pending->concat(
            IpApplication::with('user')
                ->where('status', 'Clerk Review In-Progress')
                ->orderBy('created_at', 'asc')
                ->take(5)
                ->get()
                ->map(fn($item) => tap($item, fn($i) => $i->type = 'Import Permit'))
        );

        $pending = $pending->concat(
            InspectionApplication::with('user')
                ->where('status', 'Clerk review in-progress')
                ->orderBy('created_at', 'asc')
                ->take(5)
                ->get()
                ->map(fn($item) => tap($item, fn($i) => $i->type = 'Inspection'))
        );

        $pending = $pending->concat(
            ConsignmentApplication::with('user')
                ->where('status', 'Clerk Review In-Progress')
                ->orderBy('created_at', 'asc')
                ->take(5)
                ->get()
                ->map(fn($item) => tap($item, fn($i) => $i->type = 'Consignment'))
        );

        return $pending
            ->sortBy('created_at')
            ->take(5)
            ->map(fn($item) => tap($item, fn($i) => $i->is_overdue = $i->created_at->diffInDays(now()) > 2));
    }

    /**
     * Get active announcements (max 3).
     */
    private function getAnnouncements()
    {
        return Announcement::with('attachments')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->toDateString());
            })
            ->orderBy('pin_announcement', 'desc')
            ->latest()
            ->take(3)
            ->get();
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


    /**
     * Get applications that have been pending in Clerk review for more than 3 days.
     * Status is compared case‑insensitively.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getOverduePendingAppsCount(): int
    {
        $count = 0;

        $count += IpApplication::whereRaw('LOWER(status) = ?', ['clerk review in-progress'])
            ->where('created_at', '<', now()->subDays(3))
            ->count();

        $count += InspectionApplication::whereRaw('LOWER(status) = ?', ['clerk review in-progress'])
            ->where('created_at', '<', now()->subDays(3))
            ->count();

        $count += ConsignmentApplication::whereRaw('LOWER(status) = ?', ['clerk review in-progress'])
            ->where('created_at', '<', now()->subDays(3))
            ->count();

        return $count;
    }
}
