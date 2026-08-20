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
        Schema::table('consignment_applications', function (Blueprint $table) {
            $table->json('vehicle_ids')->nullable()->after('importer_verify');
            $table->string('ptn_number')->nullable()->after('vehicle_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consignment_applications', function (Blueprint $table) {
            $table->dropColumn(['vehicle_ids', 'ptn_number']);
        });
    }
};