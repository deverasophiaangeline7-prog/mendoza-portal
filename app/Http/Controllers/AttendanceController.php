<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display the main grid of grade levels (attendance.blade.php).
     * Route: /attendance
     */
    public function index()
    {
        // This ensures it loads the file: resources/views/attendance.blade.php
        return view('attendance');
    }

    /**
     * Display the attendance list for a specific grade.
     * Route: /attendance/{grade}
     */
   public function show($grade)
{
    // 1. Define the Grade Names Mapping
    $gradeNames = [
        'nursery'     => 'Nursery - St. Mary',
        'kinder'      => 'Kinder - St. Bridget',
        'preparatory' => 'Preparatory - St. Augustine',
        'grade-1'     => 'Grade 1 - Faith',
        'grade-2'     => 'Grade 2 - Hope',
        'grade-3'     => 'Grade 3 - Love',
        'grade-4'     => 'Grade 4 - Grace',
        'grade-5'     => 'Grade 5 - Light',
        'grade-6'     => 'Grade 6 - Wisdom',
    ];

    // 2. Create the $displayName variable (This fixes your error!)
    $displayName = $gradeNames[$grade] ?? strtoupper(str_replace('-', ' ', $grade));

    // 3. Define your dummy students list
    $students = [
        ['name' => 'ARBOLEDA, ERROL GABRIEL P.', 'gender' => 'Male'],
        ['name' => 'AYING, LIAM-J C.', 'gender' => 'Male'],
        ['name' => 'ALAMA, CAITLYN JACE S.', 'gender' => 'Female'],
        ['name' => 'ALLANA, ABRYL R.', 'gender' => 'Female'],
    ];

    // 4. Return the view with all required variables
    return view('section-attendance', [
        'grade' => $grade,
        'displayName' => $displayName,
        'students' => $students,
        'eventsData' => [],
        'canManage' => auth()->user()->role === 'teacher'
    ]);
    }
    
    public function create()
{
    $students = [
        (object)['id' => 1, 'name' => 'ALEXANDER, GABRIEL'],
        (object)['id' => 2, 'name' => 'MENDOZA, SOPHIA'],
        // ... more students
    ];
    $date = now()->format('Y-m-d');
    
    return view('teacher-attendance', compact('students', 'date'));
}
}