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
        Schema::create('consignment_conditions', function (Blueprint $table) {
            $table->id();
            $table->integer('category')->comment("from public_code table: condition_category");
            $table->text('item_name');
            $table->longText('addional_condition');
            $table->float('quantity_limit')->nullable();
            $table->date('date_limit')->nullable();
            $table->text('country')->comment('in JSON form');
            $table->text('usage')->comment('in JSON form');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_conditions');
    }
};
