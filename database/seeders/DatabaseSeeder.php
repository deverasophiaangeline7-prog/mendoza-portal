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
    $adminId = 'admin@mendoza.edu.ph';
    User::create([
        'username' => $adminId,
        'email'    => $adminId, 
        'password' => Hash::make('password123'),
        'role'     => 'admin',
        'status'   => 'active',
    ]);

    // 3. Teacher Account & Profile
    $teacherId = 'teacher@mendoza.edu.ph';
    $teacherAccount = User::create([
        'username' => $teacherId,
        'email'    => $teacherId,
        'password' => Hash::make('Teacher123!'),
        'role'     => 'teacher',
        'status'   => 'active',
    ]);

    \App\Models\Teacher::create([
        'user_id'    => $teacherAccount->user_id,
        'first_name' => 'Test',
        'last_name'  => 'Teacher',
        'advisory'   => 1, // St. Mary
    ]);

    // 4. Parent Account (This creates the missing $parentAccount variable)
    $parentId = 'testparent@gmail.com';
    $parentAccount = User::create([
        'username' => $parentId,
        'email'    => $parentId, 
        'password' => Hash::make('Parent123!'),
        'role'     => 'parent',
        'status'   => 'active',
    ]);

    // 5. Student Profile (Uses the variable from step 4)
    \App\Models\Student::create([
        'user_id'     => $parentAccount->user_id,
        'lrn'         => '123456789012',
        'first_name'  => 'Juan',
        'last_name'   => 'Dela Cruz',
        'gender'      => 'Male',
        'birth_date'  => '2018-05-15',
        'grade_level' => 'Nursery',
        'section_id'  => 1, // St. Mary
    ]);
}
}