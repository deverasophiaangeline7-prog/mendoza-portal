<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolCalendar extends Model
{
    protected $table = 'school_calendar';
    protected $primaryKey = 'calendar_id';

    protected $fillable = [
    'start_date',
    'event_title',
    'time',
    'description', 
];

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}