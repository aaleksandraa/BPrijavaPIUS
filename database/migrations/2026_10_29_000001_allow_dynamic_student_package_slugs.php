<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The original enum only accepts pius-plus and pius-pro. Changing the
        // column also removes SQLite's CHECK and MySQL's native enum, which the
        // older PostgreSQL-only DROP CONSTRAINT migration did not cover.
        Schema::table('students', function (Blueprint $table) {
            $table->string('package_type')->change();
        });
    }

    public function down(): void
    {
        // Keep the string column: restoring the two-value enum would reject
        // students already enrolled in courses created through the admin panel.
    }
};
