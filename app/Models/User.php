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
        'section_id',
        'customer_name',
    ];

    /**
     * RELATIONSHIPS
     * These allow you to do things like $user->student->lrn
     */

    // Link to Student 
    
    public function admin()
    {
        return $this->hasOne(Admin::class, 'user_id', 'user_id');
    }

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

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id', 'user_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id', 'user_id');
    }

    /**
     * Unified Name Attribute Accessor
     */
    public function getNameAttribute(): ?string
    {
        // If the user has a linked admin profile
        if ($this->admin) {
            return implode(' ', array_filter([
                $this->admin->first_name, 
                $this->admin->middle_name, 
                $this->admin->last_name
            ]));
        }
        
        // If the user has a linked student profile
        if ($this->student) {
            return implode(' ', array_filter([
                $this->student->first_name, 
                $this->student->middle_name, 
                $this->student->last_name
            ]));
        }

        // If the user has a linked teacher profile
        if ($this->teacher) {
            return implode(' ', array_filter([
                $this->teacher->first_name ?? '', 
                $this->teacher->middle_name ?? '', 
                $this->teacher->last_name ?? ''
            ]));
        }

        // If the fallback username or email is the admin email, return 'Admin'
        if ($this->username === 'admin@gmail.com' || $this->email === 'admin@gmail.com') {
            return 'Admin';
        }

        // Fallback to username if no profile name is found
        return $this->username;
    }

    public function getSectionNameAttribute()
    {
        // If the user is a student and has a section assigned
        if ($this->student && $this->student->section) {
            return $this->student->section->grade_level . ' - ' . $this->student->section->section_name;
        }

        // If the user is a teacher, you can pull their advisory section if applicable
        if ($this->sections()->exists()) {
            return $this->sections()->first()->name ?? null;
        }

        return null;
    }

    public function latestMessageWithAuthUser()
    {
        // SMART QUERY: Check if this user is actually a virtual Group Chat
        if (!is_null($this->custom_name)) {
            return \App\Models\Message::where('receiver_id', $this->user_id)
                                      ->latest('id') // Fixed!
                                      ->first();
        }

        // Standard 1-on-1 Chat Query
        return \App\Models\Message::where(function($query) {
            $query->where('sender_id', auth()->id())
                  ->where('receiver_id', $this->user_id);
        })->orWhere(function($query) {
            $query->where('sender_id', $this->user_id)
                  ->where('receiver_id', auth()->id());
        })->latest('id')->first();
    }

    public function unreadMessagesCount()
    {
        // GROUP CHAT UNREAD LOGIC
        if (!is_null($this->custom_name)) {
            $allMessageIds = \App\Models\Message::where('receiver_id', $this->user_id)->pluck('id');
            $readCount = \App\Models\MessageRead::where('user_id', auth()->id())
                                                ->whereIn('message_id', $allMessageIds)
                                                ->count();
            return max(0, $allMessageIds->count() - $readCount); 
        }

        // 1-ON-1 CHAT UNREAD LOGIC
        return \App\Models\Message::where('sender_id', $this->user_id)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->count();
    }

    public function isOnline()
    {
        return \Illuminate\Support\Facades\Cache::has('user-is-online-' . $this->user_id);
    }

    public function getCustomNameAttribute($value)
    {
        if (\Illuminate\Support\Str::startsWith($this->username, 'gc_section_') && $this->section_id) {
            $section = \App\Models\Section::find($this->section_id);
            if ($section) {
                return strtoupper($section->grade_level . ' - ' . $section->section_name . ' (Official)');
            }
        }
        
        return $value;
    }
}