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
        Schema::create('notifications', function (Blueprint $table) {
            // $table->uuid('id')->primary();
            // $table->string('type');
            // $table->morphs('notifiable');
            // $table->text('data');
            // $table->timestamp('read_at')->nullable();
            // $table->timestamps();

            $table->uuid('id')->primary(); // Optional, leave Laravel default if you like
            $table->string('notifiable_type');
            $table->uuid('notifiable_id'); // <-- CHANGE FROM BIGINT
            $table->text('type');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
