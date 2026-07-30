<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MidtransSystemUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'system-midtrans@internal.local',
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'System - Midtrans Gateway',
                'username' => 'system_midtrans',
                'password' => Hash::make(Str::random(40)),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
