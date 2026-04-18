<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $primaryKey = 'student_id'; 

    protected $fillable = [
        'user_id',
        'lrn',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'birth_date',
        'grade_level',
        'section_id'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function section() { 
        return $this->belongsTo(Section::class, 'section_id'); 
    }
}