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
        Schema::create('exporter', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('phone_no', 25);
            $table->text('address');
            $table->string('country', 50);

            // UUID FK
            $table->uuid('registered_by');

            $table->timestamps();

            $table->foreign('registered_by')->references('uuid')->on('public_users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exporter');
    }
};
