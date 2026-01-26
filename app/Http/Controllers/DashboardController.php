<?php

namespace App\Http\Controllers;

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
    public function dashboard()
    {
        // ✅ Check which guard is logged in
        if (Auth::guard('public')->check()) {
            return $this->public_dashboard();
        }

        if (Auth::guard('internal')->check()) {
            return $this->internal_dashboard();
        }

        // ❌ If no guard is logged in, redirect to login
        return redirect()->route('login');
    }

    protected function public_dashboard()
    {
        // $notifications = auth()->user()->notifications()->latest()->take(10)->get();
        $notifications = []; // Public users may not have notifications

        return view('pages.public.dashboard', [
            'notifications' => $notifications,
        ]); // Public user dashboard
    }

    protected function internal_dashboard()
    {
        // $notifications = Notification::where('notifiable_type', 'internal')
        //     ->where('notifiable_id', authUser()['user']->uuid)
        //     ->latest()
        //     ->take(10)
        //     ->get();

        return view('dashboard.internal.main-dashboard', [
            // 'notifications' => $notifications,
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
