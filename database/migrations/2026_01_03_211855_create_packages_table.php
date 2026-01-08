<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // PIUS PLUS, PIUS PRO
            $table->string('slug')->unique(); // pius-plus, pius-pro
            $table->decimal('price', 10, 2); // 1800.00, 2500.00
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('duration_days')->default(60);
            $table->json('features')->nullable(); // Lista feature-a
            $table->timestamps();
        });

        // Tabela za rate paketa
        Schema::create('package_installments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('package_id');
            $table->integer('installment_number'); // 1, 2, 3
            $table->decimal('amount', 10, 2); // 400, 500, 900
            $table->string('due_description'); // "u roku od 48h", "do kraja februara"
            $table->date('due_date')->nullable(); // Konkretni datum ako postoji
            $table->integer('due_days')->nullable(); // Ili broj dana od potpisa
            $table->timestamps();

            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_installments');
        Schema::dropIfExists('packages');
    }
};
