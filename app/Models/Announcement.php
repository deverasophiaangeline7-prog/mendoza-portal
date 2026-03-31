<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $primaryKey = 'announcement_id';

    protected $fillable = [
        'posted_by',
        'title',
        'content',
        'scope',
        'status',
        'date_posted',
    ];

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}