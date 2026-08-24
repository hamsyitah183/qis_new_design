<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('public_users', function (Blueprint $table) {
            $table->json('person_in_charge')->nullable()->after('verification_attachment');
        });
    }

    public function down()
    {
        Schema::table('public_users', function (Blueprint $table) {
            $table->dropColumn('person_in_charge');
        });
    }
};
