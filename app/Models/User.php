<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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

}