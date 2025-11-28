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
        Schema::create('checkout_item', function (Blueprint $table) {
            $table->id();
            $table->uuid('ref_checkout')->index();
            $table->integer('item_id')->index();
            $table->string('item_from')->comment('eg: import_permit, consignment_permit, consignment_attachment');
            $table->timestamps();

            $table->foreign('ref_checkout')->references('uuid')->on('checkout')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkout_item');
    }
};
