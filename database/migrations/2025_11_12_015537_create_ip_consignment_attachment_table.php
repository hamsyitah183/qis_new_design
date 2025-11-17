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
        Schema::create('ip_consignment_attachment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permit_id'); // FK to ip_consignment_permit
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_del')->default(false)->comment('1 means deleted, 0 means active');
            $table->timestamps();

            $table->foreign('permit_id')
                ->references('id')
                ->on('ip_consignment_permit');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_consignment_attachment');
    }
};
