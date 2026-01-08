<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->string('invoice_number')->unique(); // 2026/001
            $table->date('invoice_date');
            $table->date('payment_date')->nullable(); // Datum plaćanja
            $table->string('description'); // Kurs Pius Plus 1. Rata
            $table->decimal('net_amount', 10, 2); // Netto cijena
            $table->decimal('vat_rate', 5, 2)->default(20.00); // PDV stopa
            $table->decimal('vat_amount', 10, 2); // PDV iznos
            $table->decimal('total_amount', 10, 2); // Ukupno sa PDV
            $table->integer('installment_number')->nullable(); // Koja rata (1, 2, 3)
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
