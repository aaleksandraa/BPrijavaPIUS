<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove the CHECK constraint on package_type column
        DB::statement('ALTER TABLE students DROP CONSTRAINT IF EXISTS students_package_type_check');
    }

    public function down(): void
    {
        // Re-add the constraint if needed (optional)
        DB::statement("ALTER TABLE students ADD CONSTRAINT students_package_type_check CHECK (package_type IN ('pius-plus', 'pius-pro'))");
    }
};
