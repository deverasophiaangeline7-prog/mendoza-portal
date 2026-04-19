<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $primaryKey = 'section_id';
    protected $fillable = [
    'section_name',
    'grade_level',
    'teacher_id', // Add this line!
];

    // A section has many students
    public function students()
    {
        return $this->hasMany(Student::class, 'section_id');
    }

    public function teacher() 
    {
    return $this->belongsTo(User::class, 'teacher_id', 'user_id');
    }

}