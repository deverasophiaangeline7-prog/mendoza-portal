<?php

// app/Models/Appointment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id', 'parent_id', 'discussion_topic', 
        'appointment_date', 'start_time', 'end_time', 'status'
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}