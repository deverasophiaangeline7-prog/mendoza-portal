<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Message;
use App\Models\User;

class ChatSystem extends Component
{
    public $selectedConversationId;

    public function selectConversation($userId)
    {
        $this->selectedConversationId = $userId;
        
        // Mark messages as read when opened
        Message::where('sender_id', $userId)
               ->where('receiver_id', auth()->id())
               ->update(['is_read' => true]);
    }

    public function render()
    {
        // Get list of users you have chatted with (simplified)
        $users = User::where('id', '!=', auth()->id())->get();

        $messages = [];
        if ($this->selectedConversationId) {
            $messages = Message::where(function($query) {
                $query->where('sender_id', auth()->id())
                      ->where('receiver_id', $this->selectedConversationId);
            })->orWhere(function($query) {
                $query->where('sender_id', $this->selectedConversationId)
                      ->where('receiver_id', auth()->id());
            })->orderBy('created_at', 'asc')->get();
        }

        return view('livewire.chat-system', compact('users', 'messages'));
    }
}