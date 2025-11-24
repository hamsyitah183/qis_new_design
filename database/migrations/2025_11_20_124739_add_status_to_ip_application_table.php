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
        Schema::table('ip_application', function (Blueprint $table) {
            //
            $table->string('status')->nullable()->after('category_application')->default('submitted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ip_application', function (Blueprint $table) {
            //
        });
    }
};
