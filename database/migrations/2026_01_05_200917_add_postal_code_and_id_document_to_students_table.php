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
        Schema::table('students', function (Blueprint $table) {
            $table->string('postal_code')->nullable()->after('address');
            $table->string('id_document_number')->nullable()->after('email');
            $table->string('company_postal_code')->nullable()->after('company_address');
            $table->string('company_city')->nullable()->after('company_postal_code');
            $table->string('company_country')->nullable()->after('company_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['postal_code', 'id_document_number', 'company_postal_code', 'company_city', 'company_country']);
        });
    }
};
