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
        'term1',
        'term2',
        'term3',
    ];
}