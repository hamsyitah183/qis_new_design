<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ip_condition', function (Blueprint $table) {
            $table->string('item_bahasa')->nullable()->after('item_name');
        });

        Schema::table('consignment_conditions', function (Blueprint $table) {
            $table->string('item_bahasa')->nullable()->after('item_name');
        });
    }

    public function down()
    {
        Schema::table('ip_condition', function (Blueprint $table) {
            $table->dropColumn('item_bahasa');
        });

        Schema::table('consignment_conditions', function (Blueprint $table) {
            $table->dropColumn('item_bahasa');
        });
    }
};