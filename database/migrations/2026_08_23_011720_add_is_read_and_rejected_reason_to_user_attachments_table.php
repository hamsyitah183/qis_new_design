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
        Schema::table('user_attachments', function (Blueprint $table) {
            $table->boolean('is_read')->default(false)->after('description');
            $table->text('rejected_reason')->nullable()->after('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_attachments', function (Blueprint $table) {
            $table->dropColumn(['is_read', 'rejected_reason']);
        });
    }
};