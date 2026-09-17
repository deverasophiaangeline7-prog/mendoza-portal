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
        
        if ($apiKey && Auth::user()->role === 'parent') {
            $receiver = User::find($request->receiver_id);

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

            $history = Message::where(function($query) use ($request) {
                $query->where('sender_id', Auth::id())->where('receiver_id', $request->receiver_id);
            })->orWhere(function($query) use ($request) {
                $query->where('sender_id', $request->receiver_id)->where('receiver_id', Auth::id());
            })
            ->where('id', '!=', $currentMessage->id) 
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

            $activeYear = \App\Models\SchoolYear::where('status', 'active')->first();
            $termInfo = "- Term schedules are not set yet.\n";
            $lastDayOfSchool = "TBA";
            
            if ($activeYear) {
                $t1s = $activeYear->term1_start ? \Carbon\Carbon::parse($activeYear->term1_start)->format('F d, Y') : 'TBA';
                $t1e = $activeYear->term1_end ? \Carbon\Carbon::parse($activeYear->term1_end)->format('F d, Y') : 'TBA';
                $t2s = $activeYear->term2_start ? \Carbon\Carbon::parse($activeYear->term2_start)->format('F d, Y') : 'TBA';
                $t2e = $activeYear->term2_end ? \Carbon\Carbon::parse($activeYear->term2_end)->format('F d, Y') : 'TBA';
                $t3s = $activeYear->term3_start ? \Carbon\Carbon::parse($activeYear->term3_start)->format('F d, Y') : 'TBA';
                $t3e = $activeYear->term3_end ? \Carbon\Carbon::parse($activeYear->term3_end)->format('F d, Y') : 'TBA';

                $termInfo = "- Term 1: {$t1s} to {$t1e}.\n" .
                            "- Term 2: {$t2s} to {$t2e}.\n" .
                            "- Term 3: {$t3s} to {$t3e}.\n";
                            
                $lastDayOfSchool = $t3e;
            }

            // UPDATED SYSTEM PROMPT: Now tells AI to IGNORE complex/unknown questions
            $systemPrompt = "You are the automated virtual assistant for Mendoza Academy, Inc. 
            
            IMPORTANT: You are currently responding on behalf of a {$receiverRole} account.
            
            Guidelines:
            - Maintain a polite, professional, and helpful tone.
            - STRICT LANGUAGE MATCHING: You MUST reply in the exact same language as the user's current question.
            - Use the [PREVIOUS CHAT HISTORY] to understand the context of the user's current question.
            - Convert dates to friendly natural language (e.g., 'September 3, 2026').
            - Answer using ONLY the provided facts below.
            - If the user's message is just a greeting (e.g., 'hello'), a short phrase, or is NOT a clear question, respond with exactly one word: IGNORE.
            - If the user asks a complex question that CANNOT be answered using these exact facts, respond with exactly one word: IGNORE.

            *** MENDOZA ACADEMY CHEAT SHEET ***\n\n"
                . "[PREVIOUS CHAT HISTORY FOR CONTEXT]\n" . $historyContext . "\n\n"
                . "[ACCOUNT & SETTINGS]\n"
                . "- Passwords (reset, change, forgot): Users can change it in 'Student Information' or use the 'Forgot Password' link on the login page (which requires an email code for security). Alternatively, the Admin can change the password for them.\n"
                . "- Email Address: The email address is fixed and cannot be changed.\n"
                . "- Appointments (Cancel or Reschedule): Users can cancel or reschedule appointments, but they must choose a new time. It is subject to the teacher's availability.\n\n"
                . "[TUITION & FEES]\n"
                . "- Tuition is 1,000 PHP per month. Miscellaneous fee is 3,500 PHP.\n"
                . "- Tuition fee payment schedule: Every second Friday of the month.\n\n"
                . "[SCHOOL YEAR & TERMS]\n"
                . $termInfo
                . "- Last day of classes (School year ends): {$lastDayOfSchool}.\n\n"
                . "[GRADES RELEASE & DEADLINES]\n"
                . "- Teachers receive an automated system alert exactly 1 week before the end of each term to remind them to finalize grades.\n"
                . "- Grades are released via the Report Card module 1 to 2 weeks after the end of each Term.\n\n"
                . "[UPCOMING CALENDAR EVENTS]\n"
                . $eventsKnowledge;

            // RESTORED 2.5 API VERSION
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=' . $apiKey;

            $data = [
                "systemInstruction" => ["parts" => [["text" => $systemPrompt]]],
                "contents" => [["parts" => [["text" => $request->message]]]],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_NONE"],
                    ["category" => "HARM_CATEGORY_HATE_SPEECH", "threshold" => "BLOCK_NONE"],
                    ["category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold" => "BLOCK_NONE"],
                    ["category" => "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold" => "BLOCK_NONE"]
                ]
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
            $aiText = "IGNORE"; // Default to ignore if something goes wrong
            
            if (isset($responseData->candidates[0]->content->parts[0]->text)) {
                $aiText = trim($responseData->candidates[0]->content->parts[0]->text);
            } elseif (isset($responseData->error)) {
                $aiText = "API ERROR: " . $responseData->error->message;
            }

            // NEW SILENT LOGIC:
            if (strpos($aiText, 'IGNORE') !== false) {
                // Do absolutely nothing. The AI stays silent and lets the human reply.
            } else {
                // The AI knows the answer and replies!
                Message::create([
                    'sender_id' => $request->receiver_id, 
                    'receiver_id' => Auth::id(),          
                    'content' => "🤖 AI Assistant: " . $aiText,
                    'is_read' => false,
                ]);
            }
        }

        // ==========================================
        // 3. AJAX RESPONSE TRIGGER
        // ==========================================
        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

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
        $users = User::where('user_id', '!=', $authUser->user_id)
            ->where(function ($query) use ($authUser) {
                
                if ($authUser->role === 'admin') {
                    $query->whereNotNull('user_id'); 
                } 
                elseif ($authUser->role === 'teacher') {
                    $teacherSectionIds = \App\Models\Section::where('teacher_id', $authUser->user_id)->pluck('section_id')->toArray();
                    
                    $query->whereIn('role', ['admin', 'teacher'])
                          ->orWhere(function($q) use ($teacherSectionIds) {
                              $q->where('role', 'parent')
                                ->whereIn('section_id', $teacherSectionIds);
                          });
                } 
                elseif ($authUser->role === 'parent') {
                    $mySection = \App\Models\Section::where('section_id', $authUser->section_id)->first();
                    $myTeacherId = $mySection ? $mySection->teacher_id : null;

                    $query->where('role', 'admin');
                    
                    if ($myTeacherId) {
                        $query->orWhere('user_id', $myTeacherId);
                    }
                }
            })
            ->get();

        return $users->sortBy(function($user) {
            return $user->name;
        })->values();
    }
}