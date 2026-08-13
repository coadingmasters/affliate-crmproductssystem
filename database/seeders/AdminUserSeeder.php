<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the default admin account.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@medalert.com'],
            [
                'name' => 'Med Alert Admin',
                'password' => 'admin123',
                'role' => 'admin',
            ],
        );
    }
}
