<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password
        {email : Admin email address}
        {password : New password}';

    protected $description = 'Create an active admin user or reset an existing admin password.';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $password = (string) $this->argument('password');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Email adresa nije validna.');

            return self::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('Lozinka mora imati najmanje 8 karaktera.');

            return self::FAILURE;
        }

        $admin = AdminUser::updateOrCreate(
            ['email' => $email],
            [
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $admin->tokens()->delete();

        $this->info("Admin nalog je spreman: {$admin->email}");

        return self::SUCCESS;
    }
}
