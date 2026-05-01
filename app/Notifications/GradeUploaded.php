<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GradeUploaded extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function via($notifiable) { return ['database']; }

public function toDatabase($notifiable)
{
    return [
        'message' => 'New grades have been posted for your child.',
        'type' => 'GradeUploaded' 
    ];
}
}
