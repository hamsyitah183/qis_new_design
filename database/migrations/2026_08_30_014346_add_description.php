<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consignment_application_attachments', function (Blueprint $table) {
            $table->text('description')->nullable()->after('file_type');
        });
    }

    public function down(): void
    {
        Schema::table('consignment_application_attachments', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};