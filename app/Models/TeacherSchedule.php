<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSchedule extends Model
{
    use HasFactory;

    // Define the table name (optional, but good practice if not standard)
    protected $table = 'teacher_schedules';

    // Allow these columns to be saved (mass assigned)
    protected $fillable = [
        'teacher_id',
        'date',
        'time_slot', 
        'status',
    ];

    // Connect it back to the User model so you can access $schedule->teacher
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}