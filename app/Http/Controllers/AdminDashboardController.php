<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\IpApplication;
use App\Models\InspectionApplication;
use App\Models\ConsignmentApplication;

class AdminDashboardController extends Controller
{
    //
    public function dailyVolume()
    {
        $days = [];
        $totalData = [];
        $ipData = [];
        $inspectionData = [];
        $consignmentData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $days[] = $date->format('D, d M');

            $ip = IpApplication::whereDate('created_at', $date)->count();
            $inspection = InspectionApplication::whereDate('created_at', $date)->count();
            $consignment = ConsignmentApplication::whereDate('created_at', $date)->count();

            $ipData[] = $ip;
            $inspectionData[] = $inspection;
            $consignmentData[] = $consignment;
            $totalData[] = $ip + $inspection + $consignment;
        }

        return response()->json([
            'days' => $days,
            'series' => 
            [
                ['name' => 'Total Submissions', 'data' => $totalData], 
                ['name' => 'Import Permit', 'data' => $ipData], 
                ['name' => 'Inspection', 'data' => $inspectionData], 
                ['name' => 'Consignment', 'data' => $consignmentData]
            ],
        ]);
    }


    public function userRegistration()
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            // 'F' format specifier gives the full month name (January through December)
            // mktime() creates a timestamp, the day '1' is used to avoid issues with month-end overflows (e.g. Feb 31st)
            $months[] = date('F', mktime(0, 0, 0, $m, 1, date('Y')));
        }

        return response()->json([
            'months' => $months,

        ]);
    }
}
