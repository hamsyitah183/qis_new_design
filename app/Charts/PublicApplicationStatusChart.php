<?php

namespace App\Charts;

use App\Models\IpApplication;
use App\Models\InspectionApplication;
use App\Models\ConsignmentApplication;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\Auth;

class PublicApplicationStatusChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\DonutChart
    {
        $userId = Auth::id();

        // Drafts
        $drafts = IpApplication::where('status', '=', 'Draft')->where('user_id', '=', $userId)->count() +
                  InspectionApplication::where('status', '=', 'Draft')->where('user_id', '=', $userId)->count() +
                  ConsignmentApplication::where('status', '=', 'Draft')->where('user_id', '=', $userId)->count();

        // Pending Review
        $pending = IpApplication::whereIn('status', ['Clerk Review In-Progress', 'Awaiting Approval'])->where('user_id', '=', $userId)->count() +
                   InspectionApplication::whereIn('status', ['Clerk review in-progress', 'wait for company approval'])->where('user_id', '=', $userId)->count() +
                   ConsignmentApplication::whereIn('status', ['Clerk Review In-Progress', 'wait for company approval'])->where('user_id', '=', $userId)->count();

        // Verified / Issued (Ready for payment or already paid)
        $verified = IpApplication::where('status', '=', 'Clerk Verified')->where('user_id', '=', $userId)->count() +
                    InspectionApplication::where('status', '=', 'Clerk Verified')->where('user_id', '=', $userId)->count() +
                    ConsignmentApplication::where('status', '=', 'Clerk Verified')->where('user_id', '=', $userId)->count();

        // Rejected
        $rejected = IpApplication::where('status', 'like', '%Rejected%')->where('user_id', '=', $userId)->count() +
                    InspectionApplication::where('status', 'like', '%Rejected%')->where('user_id', '=', $userId)->count() +
                    ConsignmentApplication::where('status', 'like', '%Rejected%')->where('user_id', '=', $userId)->count();

        // Pending Payment
        $pendingPayment = IpApplication::where('user_id', $userId)
                         ->whereHas('consignmentPermits', function($q) {
                             $q->where('status', 'pending for payment');
                         })->count() +
                         InspectionApplication::where('user_id', $userId)
                         ->whereHas('inspectionItems', function($q) {
                             $q->where('status', 'pending for payment');
                         })->count() +
                         ConsignmentApplication::where('user_id', $userId)
                         ->whereHas('consignmentPermits', function($q) {
                             $q->where('status', 'pending for payment');
                         })->count();

        return $this->chart->donutChart()
            ->setTitle('Application Status')
            ->setSubtitle('Overview of your submissions')
            ->addData([$drafts, $pending, $verified, $rejected, $pendingPayment])
            ->setLabels(['Draft', 'Pending', 'Verified', 'Rejected', 'Pending Payment'])
            ->setColors(['#ABB3BB', '#FFB84D', '#2BCD95', '#F14336', '#00CFE8'])
            ->setFontFamily('inherit');
    }
}
