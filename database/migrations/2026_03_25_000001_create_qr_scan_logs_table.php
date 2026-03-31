<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qr_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->string('internal_user_uuid', 64)->nullable()->index();
            $table->string('internal_user_name')->nullable();
            $table->string('internal_user_position')->nullable();
            $table->string('scanned_value', 120)->nullable();
            $table->string('permit_number', 120)->nullable();
            $table->string('order_number', 120)->nullable();
            $table->string('application_type', 120)->nullable();
            $table->boolean('is_valid')->default(false)->index();
            $table->string('result', 16)->default('invalid');
            $table->timestamp('scanned_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_scan_logs');
    }
};
