<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RealtimeSidebarUpdate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $receiverId;
    public $senderId;
    public $messageContent;

    public function __construct($receiverId, $senderId, $messageContent)
    {
        $this->receiverId = $receiverId;
        $this->senderId = $senderId;
        $this->messageContent = $messageContent;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('sidebar.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'RealtimeSidebarUpdate';
    }

    // 👇 ITO YUNG IDINAGDAG NI COPILOT PARA SURE NA MABABASA NG JS 👇
    public function broadcastWith()
    {
        return [
            'senderId' => $this->senderId,
            'messageContent' => $this->messageContent,
        ];
    }
}