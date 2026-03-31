<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolCalendar extends Model
{
    protected $table = 'school_calendar';
    protected $primaryKey = 'calendar_id';

    protected $fillable = [
        'event_title',
        'description',
        'start_date',
        'end_date',
        'event_type',
        'is_global',
        'posted_by',
        'status',
    ];

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}