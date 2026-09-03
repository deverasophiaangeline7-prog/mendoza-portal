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
        'school_year_id',
        'core_value',
        'term1',
        'term2',
        'term3',
    ];
}