<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BehaviorReport extends Model
{
    use HasFactory;

    // Add this entire block:
    protected $fillable = [
        'student_id',
        'core_value',
        'q1',
        'q2',
        'q3',
        'q4',
    ];
}