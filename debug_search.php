<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\IpConsignmentPermit;
use App\Models\InspectionItem;
use App\Models\ConsignmentPermit;

$permitUpper = 'IPO/2608285182';
$qUpper = strtoupper($permitUpper);

foreach ([
    'Import' => IpConsignmentPermit::class,
    'Inspection' => InspectionItem::class,
    'Consignment' => ConsignmentPermit::class,
] as $label => $model) {
    $rows = $model::query()
        ->where('status', 'paid')
        ->with('application.importer', 'application.exporter')
        ->get();
    echo $label, ': ', $rows->count(), ' paid rows', PHP_EOL;

    foreach ($rows as $row) {
        $m = strtoupper((string) $row->permit_number) === $qUpper;
        echo '  ', $row->permit_number, ' | match: ', var_export($m, true), ' | app_id: ', $row->application_id, PHP_EOL;
    }
}
