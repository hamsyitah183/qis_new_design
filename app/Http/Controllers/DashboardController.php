<?php

namespace App\Http\Controllers;

use App\Charts\LineUserChart;
use App\Charts\MonthlyUsersChart;
use App\Charts\OrderDonutChart;
use App\Charts\PaymentMethodBarChart;
use App\Charts\ApplicationHorizontalChart;
use App\Models\Country;
use App\Models\IpEntryPoint;
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
    public function dashboard(LineUserChart $lineChart, OrderDonutChart $orderChart, PaymentMethodBarChart $paymentChart, ApplicationHorizontalChart $applicationChart)
    {
        // ✅ Check which guard is logged in
        if (Auth::guard('public')->check()) {
            return $this->public_dashboard();
        }

        if (Auth::guard('internal')->check()) {
            return $this->internal_dashboard($lineChart, $orderChart, $paymentChart, $applicationChart);
        }

        // ❌ If no guard is logged in, redirect to login
        return redirect()->route('login');
    }

    public function public_dashboard()
    {
        // $notifications = auth()->user()->notifications()->latest()->take(10)->get();
        $notifications = []; // Public users may not have notifications

        return view('pages.public.dashboard', [
            'notifications' => $notifications,
        ]); // Public user dashboard
    }

    public function internal_dashboard(LineUserChart $lineChart, OrderDonutChart $orderChart, PaymentMethodBarChart $paymentChart, ApplicationHorizontalChart $applicationChart)
    {
        // $notifications = Notification::where('notifiable_type', 'internal')
        //     ->where('notifiable_id', authUser()['user']->uuid)
        //     ->latest()
        //     ->take(10)
        //     ->get();

        // Fetch latest applications from all three types
        $importPermits = \App\Models\IpApplication::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'application_id' => $app->application_id,
                    'user_name' => $app->user ? $app->user->fullname : 'N/A',
                    'type' => 'Import Permit',
                    'status' => $app->status,
                    'created_at' => $app->created_at
                ];
            });

        $inspectionCerts = \App\Models\InspectionApplication::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'application_id' => $app->application_id,
                    'user_name' => $app->user ? $app->user->fullname : 'N/A',
                    'type' => 'Inspection Certificate',
                    'status' => $app->status,
                    'created_at' => $app->created_at
                ];
            });

        $consignmentCerts = \App\Models\ConsignmentApplication::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'application_id' => $app->application_id,
                    'user_name' => $app->user ? $app->user->fullname : 'N/A',
                    'type' => 'Consignment Certificate',
                    'status' => $app->status,
                    'created_at' => $app->created_at
                ];
            });

        // Combine and sort all applications
        $latestApplications = $importPermits
            ->concat($inspectionCerts)
            ->concat($consignmentCerts)
            ->sortByDesc('created_at')
            ->take(10);

        // Get statistics counts
        $totalImportPermits = \App\Models\IpApplication::count();
        $totalInspectionCerts = \App\Models\InspectionApplication::count();
        $totalConsignmentCerts = \App\Models\ConsignmentApplication::count();

        // Count all approved/accepted applications across all types
        $totalAccepted = \App\Models\IpApplication::where('status', 'Approved')->count() +
            \App\Models\InspectionApplication::where('status', 'Approved')->count() +
            \App\Models\ConsignmentApplication::where('status', 'Approved')->count();

        // Get recent activity logs
        try {
            $recentActivities = Activity::with('causer')
                ->latest()
                ->take(4)
                ->get();
        } catch (\Exception $e) {
            // If activity log fails, just show empty array
            $recentActivities = collect([]);
        }

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
            'recentActivities' => $recentActivities
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
