<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('inspection_logs');

        Schema::create('inspection_logs', function (Blueprint $table) {
            $table->id();

            $table->string('application_id')->index();

            // polymorphic user reference
            $table->uuid('causer_id')->nullable();
            $table->string('causer_type')->nullable(); // 'public' or 'internal'

            $table->string('status')->nullable();
            $table->string('action')->nullable()->comment('Action performed');
            $table->text('remark')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_logs');
    }
};
