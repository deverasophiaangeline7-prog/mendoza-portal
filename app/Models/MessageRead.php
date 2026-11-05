<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageRead extends Model
{
    protected $fillable = ['message_id', 'user_id'];

    // Para makuha natin agad ang profile picture ng nakabasa
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id');
    }
}