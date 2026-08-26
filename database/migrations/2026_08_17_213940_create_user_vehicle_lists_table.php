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
        Schema::create('user_vehicle_lists', function (Blueprint $table) {
            $table->id();

            // Reference PublicUser using UUID
            $table->uuid('user_id');

            $table->foreign('user_id')
                ->references('uuid')
                ->on('public_users')
                ->onDelete('cascade');

            $table->string('vehicle_name')->nullable();
            $table->string('vehicle_number');
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_registration_number')->nullable();

            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_vehicle_lists');
    }
};