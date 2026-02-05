<?php

namespace Database\Seeders;

use App\Models\Central\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin account
        Admin::updateOrCreate(
            ['email' => 'digitalmart.mag@gmail.com'],
            [
                'name' => 'Super Admin',
                'email' => 'digitalmart.mag@gmail.com',
                'password' => 'genius',
                'role' => 'super_admin',
                'status' => 'active',
            ]
        );

        $this->command->info('Super admin account created!');
        $this->command->info('Email: digitalmart.mag@gmail.com');
        $this->command->info('Password: genius');
    }
}

