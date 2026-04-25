<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolCalendar extends Model
{
    protected $table = 'school_calendar';

    // 1. THIS IS WHERE THE PRIMARY KEY GOES
    // Since your screenshot showed 'calendar_id', keep it as this:
    protected $primaryKey = 'calendar_id';

    // 2. ADD THIS IF YOUR ID IS A NUMBER (which it usually is)
    public $incrementing = true;

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

    public function participants()
    {
        // 3. MATCH THE RELATIONSHIP
        // We link the 'event_id' column in the participants table 
        // to the 'calendar_id' in this table.
        return $this->hasMany(EventParticipant::class, 'event_id', 'calendar_id');
    }
}