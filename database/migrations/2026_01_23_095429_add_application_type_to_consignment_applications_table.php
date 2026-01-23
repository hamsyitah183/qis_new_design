<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('consignment_applications', function (Blueprint $table) {
            $table->string('application_type')->default('Consignment Certificate')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consignment_applications', function (Blueprint $table) {
            $table->dropColumn('application_type');
        });
    }
};
