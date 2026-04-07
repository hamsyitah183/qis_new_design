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
        Schema::table('ip_condition', function (Blueprint $table) {
            $table->text('description_form')->nullable()->comment('in JSON form')->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ip_condition', function (Blueprint $table) {
            $table->dropColumn('description_form');
        });
    }
};
