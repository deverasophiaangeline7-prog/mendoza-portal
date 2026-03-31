<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementImage extends Model
{
    protected $primaryKey = 'image_id';

    protected $fillable = [
        'posted_by',
        'image_path',
        'caption',
        'status',
    ];

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}