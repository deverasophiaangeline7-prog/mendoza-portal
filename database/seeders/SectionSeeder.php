<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
{
    $sections = [
        ['section_id' => 1, 'section_name' => 'St. Mary', 'grade_level' => 'Nursery'],
        ['section_id' => 2, 'section_name' => 'St. Bridget', 'grade_level' => 'Kindergarten'],
        ['section_id' => 3, 'section_name' => 'St. Augustine', 'grade_level' => 'Preparatory'],
        ['section_id' => 4, 'section_name' => 'Faith', 'grade_level' => '1'],
        ['section_id' => 5, 'section_name' => 'Hope', 'grade_level' => '2'],
        ['section_id' => 6, 'section_name' => 'Love', 'grade_level' => '3'],
        ['section_id' => 7, 'section_name' => 'Grace', 'grade_level' => '4'],
        ['section_id' => 8, 'section_name' => 'Light', 'grade_level' => '5'],
        ['section_id' => 9, 'section_name' => 'Wisdom', 'grade_level' => '6'],
    ];

    foreach ($sections as $section) {
        \App\Models\Section::create($section);
    }
}
}
