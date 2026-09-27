<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';

    // We keep this false so it doesn't look for 'updated_at'
    public $timestamps = false; 

    protected $fillable = [
        'user_id', 
        'title', 
        'message', 
        'type', 
        'reference_id', 
        'is_read',
        'created_at' 
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Boot function to automatically stamp Manila time on creation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Automatically sets created_at to Asia/Manila time whenever a notification is created
            $model->created_at = Carbon::now('Asia/Manila');
        });
    }
}