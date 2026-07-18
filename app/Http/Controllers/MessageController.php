<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;

class MessageController extends Controller
{
    public function index()
    {
        $users = $this->getDummyChats();
        return view('chat-system', compact('users'));
    }

    public function show($id)
    {
        $users = $this->getDummyChats();
        $selectedUser = collect($users)->firstWhere('id', (int)$id);

        if (!$selectedUser) {
            abort(404, 'Chat not found');
        }

        $messages = [];

        return view('chat-system', compact('users', 'selectedUser', 'messages'));
    }

    public function store(Request $request)
    {
        return back();
    }

    // Helper method to keep our dummy data clean and pinned at the top
    private function getDummyChats()
    {
        return [
            // Pinned Chats at the top
            (object) ['id' => 3, 'name' => 'Announcements', 'type' => 'announcement'],
            (object) ['id' => 4, 'name' => '1 - Faith', 'type' => 'advisory'],
            
            // Regular Direct Messages below
            (object) ['id' => 1, 'name' => 'John Doe', 'type' => 'direct'],
            (object) ['id' => 2, 'name' => 'Jane Smith', 'type' => 'direct'],
        ];
    }
}