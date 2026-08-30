<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('qr_permit_usages', function (Blueprint $table) {
            $table->string('status', 16)->nullable()->after('used_at')->comment('approved | rejected');
            $table->decimal('used_lat', 10, 7)->nullable()->after('status');
            $table->decimal('used_lng', 10, 7)->nullable()->after('used_lat');
            $table->string('used_location', 255)->nullable()->after('used_lng');
        });
    }

    public function down(): void
    {
        Schema::table('qr_permit_usages', function (Blueprint $table) {
            $table->dropColumn(['status', 'used_lat', 'used_lng', 'used_location']);
        });
    }
};
