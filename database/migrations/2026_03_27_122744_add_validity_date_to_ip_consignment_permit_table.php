<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ip_consignment_permit', function (Blueprint $table) {
            $table->date('validity_date')->nullable()->after('remark'); 
        });
        Schema::table('consignment_permits', function (Blueprint $table) {
            $table->date('validity_date')->nullable()->after('remark'); 
        });
        Schema::table('inspection_items', function (Blueprint $table) {
            $table->date('validity_date')->nullable()->after('remark'); 
        });
    }

    public function down(): void
    {
        Schema::table('ip_consignment_permit', function (Blueprint $table) {
            $table->dropColumn('validity_date');
        });
        Schema::table('consignment_permits', function (Blueprint $table) {
            $table->dropColumn('validity_date');
        });
        Schema::table('inspection_items', function (Blueprint $table) {
            $table->dropColumn('validity_date');
        });
    }
};
