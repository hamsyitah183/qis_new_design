<?php

namespace App\Http\Controllers;

use App\Models\InternalUser;
use App\Models\PublicUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

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
        $query = $this->getFilteredQuery($request);
        return response()->json($query->get());
    }

    public function exportExcel(Request $request)
    {
        $fileName = 'activity_log_' . date('d_m_Y_H_i_s') . '.csv';
        $query = $this->getFilteredQuery($request);
        $activities = $query->get();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Date & Time', 'User', 'User Email', 'Description', 'Changes');

        $callback = function() use($activities, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($activities as $activity) {
                $user = $activity->causer ? ($activity->causer->fullname ?? $activity->causer->name ?? 'Unknown') : 'System / Unknown';
                $email = $activity->causer ? ($activity->causer->email ?? '') : '';
                
                $changes = '';
                if ($activity->properties && isset($activity->properties['attributes'])) {
                    foreach ($activity->properties['attributes'] as $key => $value) {
                         $val = is_array($value) ? json_encode($value) : $value;
                         $changes .= "$key: $val; ";
                    }
                }

                $row['Date & Time']  = $activity->created_at->format('d-m-Y H:i A');
                $row['User']    = $user;
                $row['User Email']    = $email;
                $row['Description']    = $activity->description;
                $row['Changes']  = $changes;

                fputcsv($file, array($row['Date & Time'], $row['User'], $row['User Email'], $row['Description'], $row['Changes']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request) 
    {
        $query = $this->getFilteredQuery($request);
        $activities = $query->get();

        $pdf = Pdf::loadView('pages.internal.user_management.pdf.activity_log_pdf', compact('activities'));
        
        return $pdf->download('activity_log_' . date('d_m_Y_H_i_s') . '.pdf');
    }

    private function getFilteredQuery(Request $request)
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

        return $query;
    }
}
