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
        Schema::create('consignment_applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('application_id')->unique();
            $table->string('reference_no')->nullable();
            $table->date('eta')->nullable();
            $table->enum('transport_type', ['Air', 'Sea', 'Land'])->nullable();
            $table->integer('entry_point')->nullable();

            $table->uuid('user_id')->comment('Submitted by');        // matches public_users.uuid
            $table->unsignedBigInteger('importer_id');                             // matches public_users.uuid
            $table->uuid('exporter_id');              // references exporter.id

            $table->text('importer_detail')->comment('JSON with importer info')->nullable();
            $table->tinyInteger('category_application')->default(0)->nullable();
            $table->string('importer_verify')->default('pending')->nullable();
            $table->dateTime('date_importer_verify')->nullable();
            $table->string('status')->default('submitted')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('uuid')->on('public_users')->onDelete('cascade');
            $table->foreign('importer_id')->references('id')->on('consignment_importers')->onDelete('cascade');
            $table->foreign('exporter_id')->references('uuid')->on('public_users')->onDelete('cascade');

            $table->string('application_type')->default('Consignment Certificate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_applications');
    }
};
