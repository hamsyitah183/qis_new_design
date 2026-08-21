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
        // Add to ip_condition
        Schema::table('ip_condition', function (Blueprint $table) {
            $table->string('scientific_name')->nullable()->after('addional_condition');
        });

        // Add to inspection_conditions
        Schema::table('inspection_conditions', function (Blueprint $table) {
            $table->string('scientific_name')->nullable()->after('id');
        });

        // Add to consignment_conditions
        Schema::table('consignment_conditions', function (Blueprint $table) {
            $table->string('scientific_name')->nullable()->after('addional_condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ip_condition', function (Blueprint $table) {
            $table->dropColumn('scientific_name');
        });

        Schema::table('inspection_conditions', function (Blueprint $table) {
            $table->dropColumn('scientific_name');
        });

        Schema::table('consignment_conditions', function (Blueprint $table) {
            $table->dropColumn('scientific_name');
        });
    }
};