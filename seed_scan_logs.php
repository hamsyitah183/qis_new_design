<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\InternalUser;
use App\Models\QrScanLog;

$user = InternalUser::first();
if (!$user) {
    echo 'No internal user found.', PHP_EOL;
    exit(1);
}

$permits = ['IPO/100', 'IPO/101', 'IPO/102', 'IPO/103', 'IPO/104'];
$results = ['valid', 'used', 'invalid', 'approved', 'rejected'];
$locations = ['Port Klang Gate 3', 'Port Klang Gate 5', 'KLIA Cargo', 'Penang Port', 'Johor Port'];

echo 'Inserting 100 test rows for ', $user->email, '...', PHP_EOL;

$rows = [];
for ($i = 0; $i < 100; $i++) {
    $rows[] = [
        'internal_user_uuid' => $user->uuid,
        'internal_user_name' => $user->fullname ?? '-',
        'internal_user_position' => $user->position ?? '-',
        'scanned_value' => $permits[$i % count($permits)],
        'permit_number' => $permits[$i % count($permits)],
        'order_number' => 'ORD-' . str_pad((string) (1000 + $i), 4, '0', STR_PAD_LEFT),
        'application_type' => 'Import Permit',
        'is_valid' => ($results[$i % count($results)] === 'valid') ? 1 : 0,
        'result' => $results[$i % count($results)],
        'used_lat' => 3.0 + ($i % 5) * 0.01,
        'used_lng' => 101.4 + ($i % 5) * 0.01,
        'used_location' => $locations[$i % count($locations)],
        'scanned_at' => now()->subMinutes(100 - $i),
        'created_at' => now(),
        'updated_at' => now(),
    ];
}

foreach (array_chunk($rows, 50) as $chunk) {
    QrScanLog::insert($chunk);
}

echo 'Done. Total qr_scan_logs: ', QrScanLog::count(), PHP_EOL;
