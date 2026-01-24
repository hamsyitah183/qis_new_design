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
        Schema::create('consignment_logs', function (Blueprint $table) {
            $table->id();

            // UUID foreign key referencing ip_application.application_id
            $table->uuid('application_id');
            $table->foreign('application_id')->references('application_id')->on('consignment_applications')->onDelete('cascade');

            // Polymorphic user reference
            $table->uuid('causer_id')->nullable();
            $table->string('causer_type')->nullable(); // 'public' or 'internal'

            $table->string('status')->nullable();
            $table->string('action')->nullable()->comment('Action performed');
            $table->text('remark')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_logs');
    }
};
