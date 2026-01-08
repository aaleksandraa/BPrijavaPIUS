<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->enum('payment_type', ['installments', 'fixed'])->default('installments')->after('price');
            $table->boolean('show_on_landing')->default(false)->after('is_active');
            $table->boolean('has_contract')->default(true)->after('show_on_landing');
            $table->text('contract_template')->nullable()->after('features');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'show_on_landing', 'has_contract', 'contract_template']);
        });
    }
};
