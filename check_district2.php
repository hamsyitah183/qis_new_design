<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

foreach (DB::table('districts')->get() as $d) {
    echo 'id: ', $d->id, ' | name: ', $d->name, PHP_EOL;
}
