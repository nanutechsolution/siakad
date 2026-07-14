<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin (IT / Dewa System)
        $super = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Administrator',
                'email' => 'root@unmaris.ac.id',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $this->command->info('User seeder berhasil dijalankan. Gunakan password: password');
    }
}
