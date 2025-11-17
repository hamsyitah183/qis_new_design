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
        Schema::create('approved_publics', function (Blueprint $table) {
            $table->id();

            // Refer to public_users.uuid
            $table->uuid('user_id');
            $table->foreign('user_id')
                ->references('uuid')
                ->on('public_users')
                ->onDelete('cascade');

            $table->string('verification_attachment')->nullable();

            $table->timestamp('doa_approved_time')->nullable();

            // Nullable foreign key for the approver
            $table->uuid('approved_by')->nullable();
            $table->foreign('approved_by')
                ->references('uuid')
                ->on('internal_users')
                ->onDelete('cascade');

            $table->boolean('doa_verified')->default(0);

            $table->string('status')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approved_publics');
    }
};
