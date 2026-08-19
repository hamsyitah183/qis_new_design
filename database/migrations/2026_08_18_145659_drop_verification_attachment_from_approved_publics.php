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
        Schema::table('approved_publics', function (Blueprint $table) {
            $table->dropColumn('verification_attachment');
        });
    }

    public function down()
    {
        Schema::table('approved_publics', function (Blueprint $table) {
            $table->string('verification_attachment')->nullable();
        });
    }
};
