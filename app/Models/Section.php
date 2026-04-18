<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $primaryKey = 'section_id';
    protected $fillable = ['section_name', 'grade_level'];

    // A section has many students
    public function students()
    {
        return $this->hasMany(Student::class, 'section_id');
    }
}