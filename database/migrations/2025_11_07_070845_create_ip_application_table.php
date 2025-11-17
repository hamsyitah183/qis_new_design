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
            $table->enum('transport_type',['Air', 'Sea', 'Land']);
            $table->integer('entry_point');
            $table->unsignedBigInteger('user_id')->comment('Submitted by');
            $table->unsignedBigInteger('importer_id');
            $table->unsignedBigInteger('exporter_id');
            $table->text('importer_detail')->comment('get json form during application - imp.id, imp.name, imp.phone, imp.fullAddress');
            $table->tinyInteger('category_application')->default(0)->comment('0: self Importer, 1: as agent');
            $table->boolean('importer_verify')->default(false);
            $table->dateTime('date_importer_verify')->nullable();
            $table->timestamps();

            //Foreign Key
            $table->foreign('user_id')->references('id')->on('public_users');
            $table->foreign('importer_id')->references('id')->on('public_users');
            $table->foreign('exporter_id')->references('id')->on('exporter');
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
