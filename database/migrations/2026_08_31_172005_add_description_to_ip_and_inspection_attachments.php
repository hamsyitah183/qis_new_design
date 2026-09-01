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
        if (!Schema::hasColumn('ip_application_attachments', 'description')) {
            Schema::table('ip_application_attachments', function (Blueprint $table) {
                $table->text('description')->nullable()->after('file_type');
            });
        }

        if (!Schema::hasColumn('inspection_application_attachments', 'description')) {
            Schema::table('inspection_application_attachments', function (Blueprint $table) {
                $table->text('description')->nullable()->after('file_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ip_application_attachments', 'description')) {
            Schema::table('ip_application_attachments', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }

        if (Schema::hasColumn('inspection_application_attachments', 'description')) {
            Schema::table('inspection_application_attachments', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
