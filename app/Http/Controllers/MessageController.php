<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Models\SchoolCalendar; 
use Carbon\Carbon;
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
        $currentMessage = Message::create([
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
            // A. Fetch school events from the database
            $upcomingEvents = SchoolCalendar::orderBy('start_date', 'asc')->limit(10)->get();
            
            $eventsKnowledge = "";
            if ($upcomingEvents->count() > 0) {
                foreach ($upcomingEvents as $event) {
                    $timeStr = "";
                    if (!empty($event->start_time) && !empty($event->end_time)) {
                        $timeStr = " from {$event->start_time} to {$event->end_time}";
                    } elseif (!empty($event->time)) {
                        $timeStr = " at {$event->time}";
                    }
                    $descStr = !empty($event->description) ? ". Note: {$event->description}" : "";
                    $eventsKnowledge .= "- " . $event->event_title . " on " . $event->start_date . $timeStr . $descStr . "\n";
                }
            } else {
                $eventsKnowledge = "- No upcoming events scheduled.\n";
            }

            // B. Fetch the last 4 messages to give the AI "Memory" for context
            $history = Message::where(function($query) use ($request) {
                $query->where('sender_id', Auth::id())->where('receiver_id', $request->receiver_id);
            })->orWhere(function($query) use ($request) {
                $query->where('sender_id', $request->receiver_id)->where('receiver_id', Auth::id());
            })
            ->where('id', '!=', $currentMessage->id) // Exclude the message they just sent
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get()
            ->reverse();

            $historyContext = "";
            foreach ($history as $msg) {
                $sender = ($msg->sender_id == Auth::id()) ? "User" : "AI";
                $cleanText = str_replace("🤖 AI Assistant: ", "", $msg->content);
                $historyContext .= "{$sender}: {$cleanText}\n";
            }
            if (empty($historyContext)) $historyContext = "No previous messages.";

            // C. Build the highly intelligent system prompt
            $systemPrompt = "You are the automated virtual assistant for Mendoza Academy, Inc. 
            
            Guidelines:
            - Maintain a polite, professional, and helpful tone.
            - STRICT LANGUAGE MATCHING: You MUST reply in the exact same language as the user's current question. If they ask in English, reply in English. If they ask in Tagalog, reply in Tagalog. Do not mix languages unless the user does.
            - Use the [PREVIOUS CHAT HISTORY] to understand the context of the user's current question (e.g. if they ask 'when is the next one?').
            - Convert dates to friendly natural language (e.g., 'September 3, 2026').
            - Answer using ONLY the provided facts below.
            - If the question cannot be answered using these exact facts, respond with exactly one word: ESCALATE.

            *** MENDOZA ACADEMY CHEAT SHEET ***\n\n"
                . "[PREVIOUS CHAT HISTORY FOR CONTEXT]\n" . $historyContext . "\n\n"
                . "[TUITION & FEES]\n"
                . "- Tuition is 1,000 PHP per month. Miscellaneous fee is 3,500 PHP.\n"
                . "- Tuition fee payment schedule: Every second Friday of the month.\n\n"
                . "[SCHOOL YEAR & TERMS]\n"
                . "- School year starts: June 08, 2026 for SY 2026-2027.\n"
                . "- Term 1: June 08 - September 15, 2026.\n"
                . "- Term 2: September 16 - December 18, 2026.\n"
                . "- Term 3: January 04 - April 08, 2027.\n"
                . "- Last day of classes (School year ends): April 08, 2027.\n\n"
                . "[GRADES RELEASE]\n"
                . "- Grades are released via the Report Card module 1 to 2 weeks after the end of each Term.\n\n"
                . "[UPCOMING CALENDAR EVENTS]\n"
                . $eventsKnowledge;

            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent?key=' . $apiKey;

            $data = [
                "systemInstruction" => ["parts" => [["text" => $systemPrompt]]],
                "contents" => [["parts" => [["text" => $request->message]]]]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            curl_close($ch);

            $responseData = json_decode($response);
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
            } else {
                Message::create([
                    'sender_id' => $request->receiver_id,
                    'receiver_id' => Auth::id(),
                    'content' => "🤖 AI Assistant: I'm sorry, I don't have that information. I have escalated your question to the staff.",
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