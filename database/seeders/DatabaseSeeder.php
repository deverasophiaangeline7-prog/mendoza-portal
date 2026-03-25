<?php

namespace Database\Seeders;

use App\Models\User; // Check if this line is here!
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin Account
        User::create([
            'name' => 'Board of Trustees',
            'email' => 'admin@mendoza.edu.ph',
            'lrn' => null,
            'password' => Hash::make('Admin123!'),
            'role' => 'admin',
        ]);

        // Parent Account
        User::create([
            'name' => 'Test Parent',
            'email' => null,
            'lrn' => '123456789012',
            'password' => Hash::make('Parent123!'),
            'role' => 'parent',
        ]);
    }
}