<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ip_condition', function (Blueprint $table) {
            $table->json('another_name')->nullable()->after('item_bahasa');
            $table->json('attachment')->nullable()->after('another_name');
        });

        Schema::table('consignment_conditions', function (Blueprint $table) {
            $table->json('another_name')->nullable()->after('item_bahasa');
            $table->json('attachment')->nullable()->after('another_name');
        });
    }

    public function down()
    {
        Schema::table('ip_condition', function (Blueprint $table) {
            $table->dropColumn(['another_name', 'attachment']);
        });

        Schema::table('consignment_conditions', function (Blueprint $table) {
            $table->dropColumn(['another_name', 'attachment']);
        });
    }
};