<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentHistory extends Model
{
protected $fillable = ['student_id', 'school_year_id', 'grade_level', 'section_name'];
}