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
        Schema::create('consignment_importers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_no')->nullable();
            $table->text('address')->nullable();
            $table->string('country', 3);
            $table->uuid('registered_by');
            $table->timestamps();

            $table->foreign('registered_by')->references('uuid')->on('public_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importers');
    }
};
