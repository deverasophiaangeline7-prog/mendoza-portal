<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $primaryKey = 'teacher_id'; // Match ERD

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'advisory',
        'cv_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function section() 
    { 
        return $this->belongsTo(Section::class, 'advisory', 'section_id'); 
    }
}