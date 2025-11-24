<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_logs', function (Blueprint $table) {
            $table->id();

            // UUID foreign key referencing ip_application.application_id
            $table->uuid('application_id');
            $table->foreign('application_id')
                ->references('application_id')
                ->on('ip_application')
                ->onDelete('cascade');

            // Polymorphic user reference
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
        Schema::dropIfExists('ip_logs');
    }
};
