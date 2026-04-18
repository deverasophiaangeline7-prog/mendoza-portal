<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    
    public function index()
    {
        return view('report-card');
    }

    public function show($grade)
{
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

    $displayName = $gradeNames[$grade] ?? strtoupper(str_replace('-', ' ', $grade));

    // Dummy data for testing the UI grouping
    $students = [
        ['name' => 'ARBOLEDA, ERROL GABRIEL P.', 'gender' => 'Male'],
        ['name' => 'AYING, LIAM-J C.', 'gender' => 'Male'],
        ['name' => 'ALAMA, CAITLYN JACE S.', 'gender' => 'Female'],
        ['name' => 'ALLANA, ABRYL R.', 'gender' => 'Female'],
    ];

    return view('section-report-card', [
        'grade' => $grade,
        'displayName' => $displayName,
        'students' => $students
    ]);
    }
    public function showStudent($grade, $studentId)
{
    // Dummy data to fill the UI
    $studentName = "ARBOLEDA, ERROL GABRIEL P.";
    $sectionName = "PREPARATORY - ST. AUGUSTINE";

    $subjects = [
        'Language', 'English', 'Mathematics', 'Makabansa', 
        'GMRC', 'MAPEH', 'Music', 'Arts', 'PE', 'Health'
    ];

    return view('student-report-card', compact('studentName', 'sectionName', 'subjects', 'grade'));
}
}