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
        Schema::create('boundary_officers', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('uuid')->on('internal_users')->onDelete('cascade');
            $table->foreignId('ip_entry_id')->nullable();
            $table->foreign('ip_entry_id')->references('id')->on('ip_entry_point')->onDelete('cascade');

            // keep track of the statistic
            $table->json('statistic')->nullable();

            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boundary_officers');
    }
};
