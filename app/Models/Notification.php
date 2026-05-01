<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';

    // ADD THIS LINE:
    // This tells Laravel NOT to look for an 'updated_at' column
    public $timestamps = false; 

    protected $fillable = [
        'user_id', 
        'title', 
        'message', 
        'type', 
        'reference_id', 
        'is_read',
        'created_at' // Add this since we are handling timestamps manually now
    ];

protected $casts = [
    'created_at' => 'datetime',
];
}