<?php

namespace App\Http\Controllers;

use App\Models\InternalUser;
use App\Models\PublicUser;
use Carbon\Carbon;
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

    // public function data(Request $request)
    // {
    //     $query = Activity::orderBy('created_at', 'desc');

    //     // 🧩 Handle date & time filters 
    //     if (
    //         $request->filled('start_date') ||
    //         $request->filled('end_date') ||
    //         $request->filled('start_time') ||
    //         $request->filled('end_time')
    //     ) {
    //         $startDate = $request->start_date ?? now()->toDateString();
    //         $endDate   = $request->end_date ?? $startDate;

    //         // ✅ Convert 12-hour AM/PM to 24-hour format using app timezone
    //         $startTime = $request->start_time
    //             ? Carbon::createFromFormat('h:i A', $request->start_time, config('app.timezone'))->format('H:i:s')
    //             : '00:00:00';

    //         $endTime = $request->end_time
    //             ? Carbon::createFromFormat('h:i A', $request->end_time, config('app.timezone'))->format('H:i:s')
    //             : '23:59:59';

    //         // ✅ Special case: endTime = 12:00 AM -> set to 23:59:59
    //         if ($endTime === '00:00:00' && $endDate === $startDate) {
    //             $endTime = '23:59:59';
    //         }

    //         // ✅ Combine date + time (in Malaysia timezone)
    //         $startDateTime = Carbon::createFromFormat('Y-m-d H:i:s', "{$startDate} {$startTime}", config('app.timezone'));
    //         $endDateTime   = Carbon::createFromFormat('Y-m-d H:i:s', "{$endDate} {$endTime}", config('app.timezone'));

    //         // ✅ Ensure end >= start
    //         if ($endDateTime->lessThan($startDateTime)) {
    //             $endDateTime = $startDateTime->copy()->endOfDay();
    //         }

    //         // ✅ Query in DB timezone (assume same as Malaysia time)
    //         $query->whereBetween('created_at', [$startDateTime, $endDateTime]);
    //     }

    //     // ✅ Filter by causer type
    //     if ($request->filled('causer_type')) {
    //         $query->where('causer_type', $request->causer_type);
    //     }

    //     // ✅ Filter by causer IDs
    //     if ($request->filled('causer_id')) {
    //         $causerIds = is_array($request->causer_id)
    //             ? $request->causer_id
    //             : explode(',', $request->causer_id);
    //         $query->whereIn('causer_id', $causerIds);
    //     }

    //     $query->whereNotNull('causer_id');

    //     // ✅ Map results and format timestamps in Malaysia timezone
    //     $activity_log = $query->get()->map(function ($activity) {
    //         $causer = null;

    //         if ($activity->causer_type === \App\Models\InternalUser::class) {
    //             $causer = \App\Models\InternalUser::find($activity->causer_id);
    //         } elseif ($activity->causer_type === \App\Models\PublicUser::class) {
    //             $causer = \App\Models\PublicUser::find($activity->causer_id);
    //         }

    //         if (!$causer) return null;

    //         return [
    //             'id' => $activity->id,
    //             'log_name' => $activity->log_name,
    //             'description' => $activity->description,
    //             'event' => $activity->event,
    //             'subject_type' => $activity->subject_type,
    //             'subject_id' => $activity->subject_id,
    //             'causer_type' => $activity->causer_type,
    //             'causer_id' => $activity->causer_id,
    //             'properties' => $activity->properties,
    //             'created_at' => $activity->created_at->copy()->setTimezone(config('app.timezone'))->toDateTimeString(),
    //             'updated_at' => $activity->updated_at->copy()->setTimezone(config('app.timezone'))->toDateTimeString(),
    //             'causer' => [
    //                 'id' => $causer->uuid,
    //                 'name' => $causer->name ?? $causer->fullname ?? 'Unknown User',
    //                 'email' => $causer->email ?? null,
    //                 'phone' => $causer->phone ?? null,
    //                 'type' => class_basename($activity->causer_type),
    //             ],
    //         ];
    //     })->filter();

    //     return response()->json($activity_log->values());
    // }
    public function data(Request $request)
    {
        $query = Activity::orderBy('created_at', 'desc');

        // 🔹 Filter by causer type
        if ($request->filled('causer_type')) {
            $query->where('causer_type', $request->causer_type);
        }

        // 🔹 Filter by causer_id (single or multiple)
        if ($request->filled('causer_id')) {
            $causerIds = $request->causer_id;

            if (!is_array($causerIds)) {
                $causerIds = [$causerIds];
            }

            $query->whereIn('causer_id', $causerIds);
        }

        // 🔹 Start datetime
        if ($request->filled('start_date')) {
            $startTime = $request->start_time ?? '12:00 AM';

            $startDateTime = Carbon::createFromFormat(
                'Y-m-d h:i A',
                $request->start_date . ' ' . $startTime
            );

            $query->where('created_at', '>=', $startDateTime);
        }

        // 🔹 End datetime (ONLY if provided)
        if ($request->filled('end_date')) {
            $endTime = $request->end_time ?? '11:59 PM';

            $endDateTime = Carbon::createFromFormat(
                'Y-m-d h:i A',
                $request->end_date . ' ' . $endTime
            );

            $query->where('created_at', '<=', $endDateTime);
        }

        return response()->json($query->get());
    }
}
