<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\IpEntryPoint;
use Illuminate\Support\Facades\DB;

foreach (IpEntryPoint::query()->get() as $ep) {
    echo 'id: ', $ep->id, ' | entry: ', $ep->entry_name, ' | district: ', var_export($ep->district, true), PHP_EOL;
}

echo '--- public_code rows ---', PHP_EOL;
foreach (DB::table('public_code')->select('id', 'cate_name', 'cate_code', 'description')->limit(15)->get() as $pc) {
    echo 'id: ', $pc->id, ' | cate_name: ', $pc->cate_name, ' | cate_code: ', $pc->cate_code, ' | desc: ', $pc->description, PHP_EOL;
}
