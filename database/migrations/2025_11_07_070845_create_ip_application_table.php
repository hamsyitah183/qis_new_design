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
        Schema::create('ip_application', function (Blueprint $table) {
            $table->id();
            $table->uuid('application_id')->unique();
            $table->date('eta');
            $table->enum('transport_type', ['Air', 'Sea', 'Land']);
            $table->integer('entry_point');

            $table->uuid('user_id')->comment('Submitted by');        // matches public_users.uuid
            $table->uuid('importer_id');                             // matches public_users.uuid
            $table->unsignedBigInteger('exporter_id');              // references exporter.id

            $table->text('importer_detail')->comment('JSON with importer info');
            $table->tinyInteger('category_application')->default(0);
            $table->boolean('importer_verify')->default(false);
            $table->dateTime('date_importer_verify')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('uuid')->on('public_users')->onDelete('cascade');
            $table->foreign('importer_id')->references('uuid')->on('public_users')->onDelete('cascade');
            $table->foreign('exporter_id')->references('id')->on('exporter')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_application');
    }
};
