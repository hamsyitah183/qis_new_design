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
        Schema::create('ip_entry_point', function (Blueprint $table) {
            $table->id();
            $table->integer('district')->comment("from public_code table: district_entry");
            $table->text('entry_name');
            $table->enum('transport_type',['Air', 'Sea', 'Land']);
            $table->boolean('is_del')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_entry_point');
    }
};
