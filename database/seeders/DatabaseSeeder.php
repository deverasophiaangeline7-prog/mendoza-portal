<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
{
    // 1. Sections must come first
    $this->call(SectionSeeder::class);

    // 2. Admin Account
    $adminId = 'admin@gmail.com';
    User::create([
        'username' => $adminId,
        'email'    => $adminId, 
        'password' => Hash::make('password123'),
        'role'     => 'admin',
        'status'   => 'active',
    ]);
}
}