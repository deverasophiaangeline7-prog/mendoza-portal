<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    use HasFactory;

    protected $fillable = ['school_year', 
    'status',
    'term1_start', 'term1_end', 
    'term2_start', 'term2_end', 
    'term3_start', 'term3_end'];
}