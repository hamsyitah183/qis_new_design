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
        Schema::create('public_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('fullname');
            $table->string('no_ic')->unique();
            $table->string('email')->unique();
            $table->enum('account_type', ['company', 'individu']);
            $table->string('phone_number')->unique();
            $table->string('office_number')->nullable();
            $table->string('address_1');
            $table->string('address_2')->nullable();
            $table->string('postcode');
            $table->string('district');
            $table->string('state');
            $table->string('password');
            $table->boolean('doa_verified')->default(0);
            $table->string('verification_attachment')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_users');
    }
};
