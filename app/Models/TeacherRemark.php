<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherRemark extends Model
{
    use HasFactory;

    protected $table = 'teacher_remarks';

    protected $fillable = [
        'student_id',
        'school_year_id',
        'term1',
        'term2',
        'term3',
    ];
}