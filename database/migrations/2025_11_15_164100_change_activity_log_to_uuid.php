<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeActivityLogToUuid extends Migration
{
    public function up()
    {
        Schema::connection(config('activitylog.database_connection'))->table('activity_log', function (Blueprint $table) {
            // Change IDs to UUID
            $table->uuid('causer_id')->nullable()->change();
            $table->uuid('subject_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::connection(config('activitylog.database_connection'))->table('activity_log', function (Blueprint $table) {
            // Revert to BIGINT (original nullableMorphs type)
            $table->unsignedBigInteger('causer_id')->nullable()->change();
            $table->unsignedBigInteger('subject_id')->nullable()->change();
        });
    }
}
