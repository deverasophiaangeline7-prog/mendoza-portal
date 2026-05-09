<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Notification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // 1. Tell Laravel the custom Primary Key from your ERD
    protected $primaryKey = 'user_id';

    /**
     * Mass assignable attributes (Only login-related data)
     */
    protected $fillable = [
        'username',
        'password',
        'email',
        'role',
        'profile_photo_path',
    ];

    /**
     * RELATIONSHIPS
     * These allow you to do things like $user->student->lrn
     */

    // Link to Student Table
    public function student()
    {
        return $this->hasOne(\App\Models\Student::class, 'user_id', 'user_id');
    }

    // Link to Teacher Table
    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id', 'user_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'teacher_id', 'user_id');
    }


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

public function getEmailForPasswordReset()
    {
        return $this->username;
    }

    public function customNotifications()
{
    return $this->hasMany(Notification::class, 'user_id')
                ->where('is_read', 0)
                ->orderBy('created_at', 'desc');
}

/**
 * Reusable notification helper
 */
public function notifyUser($title, $message, $type = 'general')
{
    return \App\Models\Notification::create([
        'user_id'    => $this->user_id, // Uses the primary key of the user being notified
        'title'      => $title,
        'message'    => $message,
        'type'       => $type,
        'is_read'    => 0,
        'created_at' => now(),
    ]);
}
// User.php
public function sentMessages()
{
    return $this->hasMany(Message::class, 'sender_id');
}

public function receivedMessages()
{
    return $this->hasMany(Message::class, 'receiver_id');
}
}