<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    // Explicitly declaring the table name you chose!
    protected $table = 'audit_logs';
    
    protected $primaryKey = 'log_id';

    protected $fillable = [
        'user_id',
        'action',
        'description'
    ];

    // This lets us link the log entry back to the specific admin who did it
    public function user()
    {
        // Make sure 'user_id' matches the primary key in your User table
        return $this->belongsTo(User::class, 'user_id', 'user_id'); 
    }

    protected static function booted()
    {
        static::created(function ($log) {
            // Load the user relationship so Javascript has the username/name
            $log->load('user'); 
            
            // Fire the real-time event
            event(new \App\Events\NewAuditLog($log));
        });
    }
}