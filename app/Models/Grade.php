<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $primaryKey = 'grade_id';

    protected $fillable = [
        'student_id', 
        'school_year_id',
        'subject_name', 
        'q1', 
        'q2', 
        'q3', 
        'q4', 
        'final_grade', 
        'remarks',
    ];
}