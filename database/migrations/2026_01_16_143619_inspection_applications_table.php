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
        Schema::dropIfExists('inspection_applications');
        Schema::create('inspection_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_id')->nullable();

            $table->string('user_id')->nullable()->comment('References public_users.uuid');
            $table->string('importer_id')->nullable()->comment('References public_users.uuid');
            $table->unsignedBigInteger('exporter_id')->nullable();

            $table->json('importer_detail')->nullable();
            $table->date('eta')->nullable();
            $table->string('transport_type')->nullable();
            $table->unsignedBigInteger('entry_point')->nullable();
            $table->string('category_application')->nullable();
            
            $table->string('importer_verify')->default('pending')->nullable();
            $table->dateTime('date_importer_verify')->nullable();

            $table->string('status')->default('draft');

            $table->string('application_type')->default('Inspection Certificate');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_applications');
    }
};
