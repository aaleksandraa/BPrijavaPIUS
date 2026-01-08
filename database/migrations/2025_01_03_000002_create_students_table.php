<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('address');
            $table->string('city');
            $table->string('country');
            $table->string('phone');
            $table->string('email')->unique();
            $table->enum('entity_type', ['individual', 'company']);
            $table->enum('payment_method', ['full', 'installments']);
            $table->enum('package_type', ['pius-plus', 'pius-pro']);
            $table->string('company_name')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_registration')->nullable();
            $table->enum('status', ['enrolled', 'contract_signed', 'completed'])->default('enrolled');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
