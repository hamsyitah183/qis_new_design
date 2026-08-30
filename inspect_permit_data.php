<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\InspectionItem;
use App\Models\ConsignmentPermit;
use App\Models\IpConsignmentPermit;

echo "=== InspectionItem (first 2) ===\n";
foreach (InspectionItem::query()->take(2)->get() as $r) {
    echo 'permit_number: ', $r->permit_number, PHP_EOL;
    echo 'consignment_detail: ', json_encode($r->consignment_detail, JSON_PRETTY_PRINT), PHP_EOL;
    echo 'quantity: ', $r->quantity, ' | unit: ', $r->unit_measurement, ' | value: ', $r->value, PHP_EOL;
    echo '---', PHP_EOL;
}

echo "=== ConsignmentPermit (first 2) ===\n";
foreach (ConsignmentPermit::query()->take(2)->get() as $r) {
    echo 'permit_number: ', $r->permit_number, PHP_EOL;
    echo 'consignment_detail: ', json_encode($r->consignment_detail, JSON_PRETTY_PRINT), PHP_EOL;
    echo 'quantity: ', $r->quantity, ' | unit: ', $r->unit_measurement, ' | value: ', $r->value, PHP_EOL;
    echo '---', PHP_EOL;
}

echo "=== IpConsignmentPermit (first 2) ===\n";
foreach (IpConsignmentPermit::query()->take(2)->get() as $r) {
    echo 'permit_number: ', $r->permit_number, PHP_EOL;
    echo 'consignment_detail: ', json_encode($r->consignment_detail, JSON_PRETTY_PRINT), PHP_EOL;
    echo 'quantity: ', $r->quantity, ' | unit: ', $r->unit_measurement, ' | value: ', $r->value, PHP_EOL;
    echo '---', PHP_EOL;
}
