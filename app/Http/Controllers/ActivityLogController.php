<?php

namespace App\Http\Controllers;

use App\Models\InternalUser;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    //
    public function log()
    {
        return view('pages.internal.user_management.list_acitivity_log', [
            'title' => 'Activity Log'
        ]);
    }

    public function data(Request $request)
    {
        $query = Activity::orderBy('created_at', 'desc');

        // 📅 Optional date range filter
        if ($request->has(['start_date', 'end_date'])) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        // 👥 Optional causer_type filter
        if ($request->filled('causer_type')) {
            $query->where('causer_type', $request->causer_type);
        }

        $activity_log = $query->get()->map(function ($activity) {
            $causer = null;
            if ($activity->causer_type === \App\Models\InternalUser::class) {
                $causer = \App\Models\InternalUser::find($activity->causer_id);
            } elseif ($activity->causer_type === \App\Models\PublicUser::class) {
                $causer = \App\Models\PublicUser::find($activity->causer_id);
            }

            return [
                'id' => $activity->id,
                'log_name' => $activity->log_name,
                'description' => $activity->description,
                'event' => $activity->event,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'causer_type' => $activity->causer_type,
                'causer_id' => $activity->causer_id,
                'properties' => $activity->properties,
                'created_at' => $activity->created_at,
                'updated_at' => $activity->updated_at,
                'causer' => $causer ? [
                    'id' => $causer->id,
                    'name' => $causer->name ?? $causer->fullname ?? 'Unknown User',
                    'email' => $causer->email ?? null,
                    'phone' => $causer->phone ?? null,
                    'type' => class_basename($activity->causer_type),
                ] : null,
            ];
        });

        return response()->json($activity_log);
    }
}
