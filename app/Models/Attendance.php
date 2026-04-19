<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // 1. Tell Laravel our specific primary key name
    protected $primaryKey = 'attendance_id';

    // 2. Allow these columns to be saved automatically (Mass Assignment)
    protected $fillable = [
        'student_id',
        'attendance_date',
        'status',
    ];
}