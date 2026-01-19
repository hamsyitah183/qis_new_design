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
        Schema::create('consignment_permits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')
                ->comment('References consignment_applications.id')
                ->constrained('consignment_applications')
                ->onDelete('cascade');

            $table->string('permit_number', 25)->nullable();

            $table->text('consignment_detail')
                ->comment('JSON form: item details (id, category, item_name, usage)');

            $table->float('quantity');

            $table->string('unit_measurement')
                ->comment('from public_code table: unit_measurement');

            $table->float('value');

            $table->string('purpose')
                ->comment('from public_code table: consignment_purpose');
            
            $table->string('status')->nullable();

            $table->text('remark')->nullable();
            $table->string('mygap_myorganic_no')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_permits');
    }
};
