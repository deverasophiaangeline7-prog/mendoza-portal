<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $authId = Auth::id();

        // Fetch ONLY users who have a message history with the logged-in user
        $users = User::where('user_id', '!=', $authId)
            ->where(function ($query) use ($authId) {
                $query->whereHas('sentMessages', function ($q) use ($authId) {
                    $q->where('receiver_id', $authId);
                })->orWhereHas('receivedMessages', function ($q) use ($authId) {
                    $q->where('sender_id', $authId);
                });
            })
            ->get();
        
        return view('chat-system', compact('users'));
    }

    public function show($id)
    {
        $authId = Auth::id();

        // Apply the same filter for the sidebar when viewing a specific chat
        $users = User::where('user_id', '!=', $authId)
            ->where(function ($query) use ($authId) {
                $query->whereHas('sentMessages', function ($q) use ($authId) {
                    $q->where('receiver_id', $authId);
                })->orWhereHas('receivedMessages', function ($q) use ($authId) {
                    $q->where('sender_id', $authId);
                });
            })
            ->get();
        
        // Find the selected user by user_id
        $selectedUser = User::where('user_id', $id)->firstOrFail();

        // Mark incoming messages from this user as read
        Message::where('sender_id', $id)
               ->where('receiver_id', $authId)
               ->update(['is_read' => true]);

        // Fetch messages between the logged-in user and the selected user
        $messages = Message::where(function($query) use ($id, $authId) {
            $query->where('sender_id', $authId)
                  ->where('receiver_id', $id);
        })->orWhere(function($query) use ($id, $authId) {
            $query->where('sender_id', $id)
                  ->where('receiver_id', $authId);
        })->orderBy('created_at', 'asc')->get();

        return view('chat-system', compact('users', 'selectedUser', 'messages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,user_id',
            'message' => 'required|string',
        ]);

        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'content' => $request->message,
            'is_read' => false,
        ]);

        return redirect()->route('messages.show', ['id' => $request->receiver_id]);
    }
}