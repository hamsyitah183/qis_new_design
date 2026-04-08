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
        Schema::create('qr_permit_usages', function (Blueprint $table) {
            $table->id();
            $table->string('application_type', 64)->index();
            $table->string('permit_number', 120);
            $table->string('permit_number_key', 120);
            $table->string('order_number', 120)->nullable()->index();
            $table->string('used_by_uuid', 64)->nullable()->index();
            $table->timestamp('used_at')->useCurrent()->index();
            $table->timestamps();

            $table->unique(['application_type', 'permit_number_key'], 'uq_qr_permit_usage_type_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_permit_usages');
    }
};