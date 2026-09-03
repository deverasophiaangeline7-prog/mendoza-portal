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
        'term1', 
        'term2', 
        'term3', 
        'final_grade', 
        'remarks',
    ];
}