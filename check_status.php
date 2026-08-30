<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$checks = [
    'ip_consignment_permit' => ['IPO/2608285182', 'IPO/2608303511'],
    'inspection_items' => ['SP/2608307190'],
    'consignment_permits' => ['SK/2608309484'],
];

foreach ($checks as $table => $nums) {
    foreach (DB::table($table)->whereIn('permit_number', $nums)->get() as $r) {
        echo $table, ' | ', $r->permit_number, ' | status: ', var_export($r->status, true), PHP_EOL;
    }
}
