<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo implode(",", Illuminate\Support\Facades\Schema::getColumnListing('qr_permit_usages')), PHP_EOL;
echo implode(",", Illuminate\Support\Facades\Schema::getColumnListing('qr_scan_logs')), PHP_EOL;
