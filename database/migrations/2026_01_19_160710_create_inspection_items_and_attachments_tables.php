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
        Schema::dropIfExists('inspection_attachments');
        Schema::dropIfExists('inspection_items');
        
        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                ->constrained('inspection_applications')
                ->onDelete('cascade');
            $table->text('consignment_detail')->nullable();
            $table->float('quantity')->default(0);
            $table->string('unit_measurement')->nullable();
            $table->float('value')->default(0);
            $table->string('purpose')->nullable();
            $table->string('status')->default('submitted');
            $table->text('remark')->nullable();
            $table->timestamps();
        });

        Schema::create('inspection_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')
                ->constrained('inspection_items')
                ->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_attachments');
        Schema::dropIfExists('inspection_items');
    }
};
