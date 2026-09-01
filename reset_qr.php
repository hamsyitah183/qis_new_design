<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\QrScanLog;
use App\Models\QrPermitUsage;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

echo 'Before:', PHP_EOL;
echo '  qr_scan_logs: ', QrScanLog::count(), PHP_EOL;
echo '  qr_permit_usages: ', QrPermitUsage::count(), PHP_EOL;
echo '  orders with qr_used_at: ', Order::whereNotNull('qr_used_at')->count(), PHP_EOL;

DB::transaction(function () {
    QrScanLog::whereIn('result', ['used', 'approved', 'rejected', 'valid', 'invalid'])->delete();
    QrPermitUsage::query()->delete();
    Order::whereNotNull('qr_used_at')->update([
        'qr_used_at' => null,
        'qr_used_by_uuid' => null,
    ]);
});

echo 'After:', PHP_EOL;
echo '  qr_scan_logs: ', QrScanLog::count(), PHP_EOL;
echo '  qr_permit_usages: ', QrPermitUsage::count(), PHP_EOL;
echo '  orders with qr_used_at: ', Order::whereNotNull('qr_used_at')->count(), PHP_EOL;
echo 'Done. QR back to never-scanned state.', PHP_EOL;
