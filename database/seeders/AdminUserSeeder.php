<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::updateOrCreate(
            ['email' => 'admin@piusacademy.com'],
            [
                'password' => 'admin123',
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
