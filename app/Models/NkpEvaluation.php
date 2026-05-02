<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NkpEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_year_id',
        'category',
        'skill',
        'q1',
        'q2',
        'q3',
        'q4',
    ];
}
