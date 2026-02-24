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
        Schema::create('ip_consignment_permit', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')
                ->comment('References ip_application.id')
                ->constrained('ip_application') // ✅ FIXED table name
                ->onDelete('cascade');

            $table->string('permit_number', 25)->nullable();

            $table->text('consignment_detail')
                ->comment('JSON form: get from ip_condition table (id, category, item_name, usage)');

            $table->float('quantity');

            $table->string('unit_measurement')
                ->comment('from public_code table: unit_measurement');

            $table->float('value');

            $table->string('purpose')
                ->comment('from public_code table: consignment_purpose');
            
            $table->string('status')->nullable();

            $table->text('remark')->nullable();

            $table->integer('print_calc')->nullable();

            $table->text('print_reason')->nullable();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_consignment_permit');
    }
};
