<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
{
    // Admin Account
    User::create([
        'name' => 'Board of Trustees',
        'username' => 'admin', // Provided
        'email' => 'admin@mendoza.edu.ph',
        'lrn' => null,
        'password' => Hash::make('Admin123!'),
        'role' => 'admin',
    ]);

    // Teacher Account
    User::create([
        'name' => 'Test Teacher',
        'username' => 'testteacher', // Added this
        'email' => 'teacher@mendoza.edu.ph',
        'lrn' => null,
        'password' => Hash::make('Teacher123!'),
        'role' => 'teacher',
    ]);

    // Parent Account
    User::create([
        'name' => 'Test Parent',
        'username' => 'testparent', // Added this
        'email' => null,
        'lrn' => '123456789012',
        'password' => Hash::make('Parent123!'),
        'role' => 'parent',
    ]);
}
}