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
        Schema::table('consignment_applications', function (Blueprint $table) {
            // Drop FKs first - use the standard Laravel naming convention
            // Or try-catch them if they might not exist
            try {
                $table->dropForeign('consignment_applications_importer_id_foreign');
                $table->dropForeign('consignment_applications_exporter_id_foreign');
            } catch (\Exception $e) {
                // Ignore if not exists
            }

            // Now make nullable
            $table->unsignedBigInteger('importer_id')->nullable()->change();
            $table->uuid('exporter_id')->nullable()->change();

            // Re-add FKs
            $table->foreign('importer_id')->references('id')->on('consignment_importers')->onDelete('cascade');
            $table->foreign('exporter_id')->references('uuid')->on('public_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consignment_applications', function (Blueprint $table) {
            // In down, strictly speaking we should check for nulls before reverting, 
            // but we'll try to revert schema structure.
            $table->dropForeign(['importer_id']);
            $table->dropForeign(['exporter_id']);

            // Revert to nullable(false) - this will fail if actual nulls exist
            $table->unsignedBigInteger('importer_id')->nullable(false)->change();
            $table->uuid('exporter_id')->nullable(false)->change();

            $table->foreign('importer_id')->references('id')->on('consignment_importers')->onDelete('cascade');
            $table->foreign('exporter_id')->references('uuid')->on('public_users')->onDelete('cascade');
        });
    }
};
