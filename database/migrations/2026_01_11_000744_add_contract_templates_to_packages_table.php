<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Add new contract template columns
            $table->text('contract_template_individual')->nullable()->after('features');
            $table->text('contract_template_company')->nullable()->after('contract_template_individual');

            // Copy old contract_template to individual if exists
            // This will be done in a separate data migration
        });

        // Copy existing contract_template data to new columns
        DB::statement('UPDATE packages SET contract_template_individual = contract_template WHERE contract_template IS NOT NULL');
        DB::statement('UPDATE packages SET contract_template_company = contract_template WHERE contract_template IS NOT NULL');

        // Drop old column
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('contract_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Re-add old column
            $table->text('contract_template')->nullable()->after('features');
        });

        // Copy individual template back to old column
        DB::statement('UPDATE packages SET contract_template = contract_template_individual WHERE contract_template_individual IS NOT NULL');

        // Drop new columns
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['contract_template_individual', 'contract_template_company']);
        });
    }
};
