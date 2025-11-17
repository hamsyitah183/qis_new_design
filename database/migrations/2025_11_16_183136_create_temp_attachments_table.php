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
        Schema::create('temp_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('temp_name');
            $table->string('original_name');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->string('temp_path');  // storage/app/temp/filename.ext
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_attachments');
    }
};
