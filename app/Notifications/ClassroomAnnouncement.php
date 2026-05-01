<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClassroomAnnouncement extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $announcementMessage;

public function __construct($announcementMessage)
{
    $this->announcementMessage = $announcementMessage;
}

public function via($notifiable) { return ['database']; }

public function toDatabase($notifiable)
{
    return [
        'message' => $this->announcementMessage,
        'type' => 'AdviserAnnouncement' 
    ];
}
}
