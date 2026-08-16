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
        $authUser = Auth::user();

        $users = $this->getChatHistoryUsers($authId);
        $contacts = $this->getAllowedContacts($authUser);
        
        return view('chat-system', compact('users', 'contacts'));
    }

    public function show($id)
    {
        $authId = Auth::id();
        $authUser = Auth::user();

        $users = $this->getChatHistoryUsers($authId);
        $contacts = $this->getAllowedContacts($authUser);
        
        $selectedUser = User::where('user_id', $id)->firstOrFail();

        Message::where('sender_id', $id)
               ->where('receiver_id', $authId)
               ->update(['is_read' => true]);

        $messages = Message::where(function($query) use ($id, $authId) {
            $query->where('sender_id', $authId)
                  ->where('receiver_id', $id);
        })->orWhere(function($query) use ($id, $authId) {
            $query->where('sender_id', $id)
                  ->where('receiver_id', $authId);
        })->orderBy('created_at', 'asc')->get();

        return view('chat-system', compact('users', 'contacts', 'selectedUser', 'messages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,user_id',
            'message' => 'required|string',
        ]);

        // 1. Save the student's actual message to the database first
        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'content' => $request->message,
            'is_read' => false,
        ]);

        // ==========================================
        // 2. AI INTERCEPTOR LOGIC
        // ==========================================
        $apiKey = env('GEMINI_API_KEY');
        
        if ($apiKey) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.7-flash:generateContent?key=' . $apiKey;

            // FIX: "systemInstruction" MUST be camelCase, no underscores!
            $data = [
                "systemInstruction" => [
                    "parts" => [
                        ["text" => "You are the automated AI assistant for Mendoza Academy, Inc. Your only job is to answer simple FAQs using ONLY the facts provided below. Be polite and concise. Do not use markdown. If a user's question cannot be answered using these exact facts, or if they ask about specific grades, you must reply with exactly one word: ESCALATE.\n\n*** MENDOZA ACADEMY CHEAT SHEET ***\n- School Fees: Tuition is 1,000 PHP per month. Miscellaneous fee is 3,500 PHP.\n- 
                        Schedules: Morning classes start at 7:30 AM. Afternoon classes start at 1:00 PM.\n- 
                        Events: Intramurals will be held in October. Christmas break starts December 18.\n- 
                        xAnnouncements: Enrollment for next semester is ongoing until the end of the month."]
                    ]
                ],
                "contents" => [
                    ["parts" => [["text" => $request->message]]]
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            curl_close($ch);

            $responseData = json_decode($response);
            
            // SMART DIAGNOSTIC: If Google returns an error, print it to the screen instantly!
            if (isset($responseData->error)) {
                dd('Google API Error:', $responseData->error);
            }

            $aiText = "ESCALATE"; 
            if (isset($responseData->candidates[0]->content->parts[0]->text)) {
                $aiText = trim($responseData->candidates[0]->content->parts[0]->text);
            }

            if (strpos($aiText, 'ESCALATE') === false) {
                Message::create([
                    'sender_id' => $request->receiver_id, 
                    'receiver_id' => Auth::id(),          
                    'content' => "🤖 AI Assistant: " . $aiText,
                    'is_read' => false,
                ]);
            }
        }
        // ==========================================
        // END AI INTERCEPTOR LOGIC
        // ==========================================

        return redirect()->route('messages.show', ['id' => $request->receiver_id]);
    }

    private function getChatHistoryUsers($authId)
    {
        return User::where('user_id', '!=', $authId)
            ->where(function ($query) use ($authId) {
                $query->whereHas('sentMessages', function ($q) use ($authId) {
                    $q->where('receiver_id', $authId);
                })->orWhereHas('receivedMessages', function ($q) use ($authId) {
                    $q->where('sender_id', $authId);
                });
            })
            ->get();
    }

    private function getAllowedContacts($authUser)
    {
        return User::where('user_id', '!=', $authUser->user_id)
            ->where(function ($query) use ($authUser) {
                
                if ($authUser->role === 'teacher') {
                    $query->whereIn('role', ['admin', 'teacher'])
                          ->orWhere(function($subQuery) use ($authUser) {
                              $subQuery->where('role', 'parent')
                                       ->where('section_id', $authUser->section_id); 
                          });
                } elseif ($authUser->role === 'parent') {
                    $query->where('role', 'admin')
                          ->orWhere(function($subQuery) use ($authUser) {
                              $subQuery->whereIn('role', ['teacher', 'parent'])
                                       ->where('section_id', $authUser->section_id);
                          });
                } else {
                    $query->whereNotNull('user_id'); 
                }
            })->get();
    }
}