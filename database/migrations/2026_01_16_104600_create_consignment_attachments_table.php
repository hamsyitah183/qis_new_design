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
        Schema::create('consignment_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('permit_id')
                ->comment('References consignment_permits.id')
                ->constrained('consignment_permits')
                ->onDelete('cascade');

            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->text('description')->nullable();

            $table->boolean('is_del')
                ->default(false)
                ->comment('1 means deleted, 0 means active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_attachments');
    }
};
