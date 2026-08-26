<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('consignment_applications', function (Blueprint $table) {
            $table->json('prices_total')->nullable()->after('exporter_id'); // optional placement
        });
    }

    public function down()
    {
        Schema::table('consignment_applications', function (Blueprint $table) {
            $table->dropColumn('prices_total');
        });
    }
};