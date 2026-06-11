<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SAFE: run separately per table to reduce lock impact

        Schema::table('ip_consignment_permit', function (Blueprint $table) {
            $table->dateTime('validity_date')
                ->nullable()
                ->after('status');
        });

        Schema::table('inspection_items', function (Blueprint $table) {
            $table->dateTime('validity_date')
                ->nullable()
                ->after('status');
        });

        Schema::table('consignment_permits', function (Blueprint $table) {
            $table->dateTime('validity_date')
                ->nullable()
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ip_consignment_permit', function (Blueprint $table) {
            $table->dropColumn('validity_date');
        });

        Schema::table('inspection_items', function (Blueprint $table) {
            $table->dropColumn('validity_date');
        });

        Schema::table('consignment_permits', function (Blueprint $table) {
            $table->dropColumn('validity_date');
        });
    }
};