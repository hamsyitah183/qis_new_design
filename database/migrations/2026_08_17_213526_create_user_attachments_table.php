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
        Schema::create('user_attachments', function (Blueprint $table) {
            $table->id();

            // Reference PublicUser using UUID
            $table->uuid('user_id');

            $table->foreign('user_id')
                ->references('uuid')
                ->on('public_users')
                ->onDelete('cascade');

            $table->string('document_type');

            $table->string('file_path');
            $table->string('file_type');
            $table->integer('file_size');
            $table->string('original_file_name');

            $table->string('description')->nullable();

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
        Schema::dropIfExists('user_attachments');
    }
};