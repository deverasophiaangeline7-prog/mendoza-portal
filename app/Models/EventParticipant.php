<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventParticipant extends Model
{
    use HasFactory;

    // This tells Laravel these columns are allowed to be filled
    protected $fillable = [
        'event_id',
        'student_id',
        'role'
    ];

    // Connect back to the Student model so we can show their name
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');    }
}