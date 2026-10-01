<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Models\SchoolCalendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function index()
    {
        $authId = Auth::id();
        $authUser = Auth::user();
        
        $users = $this->getChatHistoryUsers($authId);
        $contacts = $this->getAllowedContacts($authUser);
        $archivedGroups = $this->getArchivedGroups($authId); 
        
        return view('chat-system', compact('users', 'contacts', 'archivedGroups'));
    }

    public function show($id)
    {
        $authId = Auth::id();
        $authUser = Auth::user();

        $users = $this->getChatHistoryUsers($authId);
        $contacts = $this->getAllowedContacts($authUser);
        $archivedGroups = $this->getArchivedGroups($authId);

        $selectedUser = User::where('user_id', $id)->firstOrFail();

        // On initial page load, mark strictly 1-on-1 chats sent FROM them TO me as read (DB update)
        if (is_null($selectedUser->custom_name)) {
            Message::where('sender_id', $id)
                   ->where('receiver_id', $authId)
                   ->where('is_read', false) // Only update if necessary
                   ->update(['is_read' => true]);
        }

        // SMART QUERY: Check if we are loading a Group Chat or a 1-on-1 Chat
        if (!is_null($selectedUser->custom_name)) {
            // Fetch ALL messages sent to this group
            $messages = Message::where('receiver_id', $id)
                                ->orderBy('created_at', 'asc')->get();
        } else {
            // Fetch strict 1-on-1 chat
            $messages = Message::where(function($query) use ($id, $authId) {
                $query->where('sender_id', $authId)
                      ->where('receiver_id', $id);
            })->orWhere(function($query) use ($id, $authId) {
                $query->where('sender_id', $id)
                      ->where('receiver_id', $authId);
            })->orderBy('created_at', 'asc')->get();
        }

        // NEW: Check if the current user has archived this group
        $isGroupArchived = false;
        if (!is_null($selectedUser->custom_name)) {
            $pivot = DB::table('group_members')->where('group_id', $id)->where('user_id', $authId)->first();
            if ($pivot && $pivot->is_archived) {
                $isGroupArchived = true;
            }
        }

        return view('chat-system', compact('users', 'contacts', 'selectedUser', 'messages', 'archivedGroups', 'isGroupArchived'));
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'group_name' => 'required|string|max:255',
            'members'    => 'required|array',
            'members.*'  => 'exists:users,user_id'
        ]);

        // 1. Create a "Virtual User" to act as the Group Chat entity
        $groupUser = new User();
        $groupUser->username    = 'group_' . uniqid();
        $groupUser->email       = 'group_' . uniqid() . '@mendoza.edu';
        $groupUser->password    = Hash::make(Str::random(16));
        $groupUser->role        = 'admin';        // Bypasses any strict enum rules
        $groupUser->custom_name = $request->group_name; // Saves into our new column!
        $groupUser->status      = 'active';
        $groupUser->save();

        // 2. Link the members to the group
        $membersData = [];
        
        // Add the creator
        $membersData[] = [
            'group_id'   => $groupUser->user_id,
            'user_id'    => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Add the selected contacts AND create notifications
        foreach ($request->members as $memberId) {
            $membersData[] = [
                'group_id'   => $groupUser->user_id,
                'user_id'    => $memberId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 🔔 Safely attempt to send notification
            try {
                DB::table('notifications')->insert([
                    'user_id'    => $memberId,
                    'title'      => 'New Group Chat',
                    'message'    => Auth::user()->name . ' added you to the group: ' . $request->group_name,
                    'type'       => 'group_chat:' . $groupUser->user_id,
                    'is_read'    => false
                ]);
            } catch (\Exception $e) {
                // If it fails, log it but DO NOT break the group creation
                Log::error('Notification Error: ' . $e->getMessage());
            }
        }

        DB::table('group_members')->insert($membersData);

        // 3. Send an initial system message
        Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $groupUser->user_id,
            'content'     => Auth::user()->name . ' created the group: ' . $request->group_name,
            'is_read'     => false,
        ]);

        return back()->with('success', 'Group created successfully!');
    }

    public function archiveGroup($id)
    {
        // 1. Personal Archive (Only hides it for the logged-in user)
        DB::table('group_members')
            ->where('group_id', $id)
            ->where('user_id', Auth::id())
            ->update(['is_archived' => true]);

        return redirect('/messages')->with('success', 'Group archived successfully!');
    }

    public function restoreGroup($id)
    {
        // 1. Personal Restore
        DB::table('group_members')
            ->where('group_id', $id)
            ->where('user_id', Auth::id())
            ->update(['is_archived' => false]);

        // 2. Safety Net: Fixes the old global testing data that got stuck
        $group = User::where('user_id', $id)->first();
        if ($group && $group->status === 'archived') {
            $group->status = 'active';
            $group->save();
        }

        return redirect('/messages/' . $id)->with('success', 'Group restored successfully!');
    }

    public function deleteGroup($id)
    {
        $group = User::where('user_id', $id)->whereNotNull('custom_name')->firstOrFail();
        DB::table('group_members')->where('group_id', $id)->delete();
        $group->delete();

        return redirect('/messages?deleted=1');
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
        // 2. AI INTERCEPTOR LOGIC (Kept as provided)
        // ==========================================
        $apiKey = env('GEMINI_API_KEY');
        
        $receiver = User::find($request->receiver_id);

        $aiResponded = false; // Add flag to track AI response

        $senderRole = strtolower(Auth::user()->role);
        $receiverRole = $receiver ? strtolower($receiver->role) : '';

        // AI triggers if ANYONE messages the Admin, OR if a PARENT messages a TEACHER
        $teacherRoles = ['teacher', 'adviser', 'faculty', 'instructor'];
        $aiShouldRespond = ($receiverRole === 'admin') || ($senderRole === 'parent' && in_array($receiverRole, $teacherRoles));

        // STRICT CHECK: Run AI only if conditions are met AND it is NOT a Group Chat
        if ($apiKey && $aiShouldRespond && $receiver && is_null($receiver->custom_name)) {
            
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
                $t1s = $activeYear->term1_start ? Carbon::parse($activeYear->term1_start)->format('F d, Y') : 'TBA';
                $t1e = $activeYear->term1_end ? Carbon::parse($activeYear->term1_end)->format('F d, Y') : 'TBA';
                $t2s = $activeYear->term2_start ? Carbon::parse($activeYear->term2_start)->format('F d, Y') : 'TBA';
                $t2e = $activeYear->term2_end ? Carbon::parse($activeYear->term2_end)->format('F d, Y') : 'TBA';
                $t3s = $activeYear->term3_start ? Carbon::parse($activeYear->term3_start)->format('F d, Y') : 'TBA';
                $t3e = $activeYear->term3_end ? Carbon::parse($activeYear->term3_end)->format('F d, Y') : 'TBA';
                
                $termInfo = "- Term 1: {$t1s} to {$t1e}.\n" .
                            "- Term 2: {$t2s} to {$t2e}.\n" .
                            "- Term 3: {$t3s} to {$t3e}.\n";
                $lastDayOfSchool = $t3e;
            }

            $currentDateString = \Carbon\Carbon::now('Asia/Manila')->format('F j, Y');

            // ==========================================
            // DYNAMIC AI PROMPTS BASED ON RECEIVER ROLE
            // ==========================================
            
            if ($currentReceiverRole === 'admin') {
                // 🛑 ADMIN PROMPT: ONLY handles Passwords and Account Settings
                $systemPrompt = "You are the automated virtual assistant for Mendoza Academy, Inc.
                IMPORTANT: You are currently responding on behalf of the Admin account.
                TODAY'S CURRENT DATE IS: {$currentDateString}. You MUST use this date as your reference point.
                
                Guidelines:
                - ALWAYS start your response with a warm, friendly, and welcoming greeting in the appropriate language (e.g., 'Hello there! 👋', 'Magandang araw po!').
                - Maintain a polite, professional, and helpful tone.
                - ALLOWED LANGUAGES: You may ONLY communicate in English or Tagalog (Filipino).
                - Answer using ONLY the provided facts below. Do not invent or assume any other information.
                - BE FORGIVING: Highly tolerate typos, incorrect spelling (e.g., 'ngayung', 'sked'), bad grammar, and very short phrases. Automatically translate Tagalog questions in your head to match the English cheat sheet facts below.

                *** STRICT 'IGNORE' RULES (CRITICAL) ***
                You MUST output exactly the word IGNORE (and nothing else) if the user's message is NOT about passwords or account settings. 
                If they ask about tuition, events, grades, schedules, term dates, specific student concerns, or just say 'hello', 'hi', 'good morning', or 'thanks', you MUST output IGNORE.

                *** ADMIN CHEAT SHEET ***
                [PREVIOUS CHAT HISTORY FOR CONTEXT]
                {$historyContext}

                [ACCOUNT & SETTINGS]
                - Passwords (reset, change, forgot): Users can change it in 'Student Information' or use the 'Forgot Password' link on the login page (which requires an email code for security). Alternatively, the Admin can change the password for them.
                - Email Address: The email address is fixed and cannot be changed.";
                
            } else {
                // 🏫 TEACHER PROMPT: Handles General School Facts (Tuition, Calendar, Terms)
                $systemPrompt = "You are the automated virtual assistant for Mendoza Academy, Inc.
                IMPORTANT: You are currently responding on behalf of a Teacher account.
                TODAY'S CURRENT DATE IS: {$currentDateString}. You MUST use this date as your reference point.
                
                Guidelines:
                - ALWAYS start your response with a warm, friendly, and welcoming greeting in the appropriate language (e.g., 'Hello there! 👋', 'Magandang araw po!').
                - Maintain a polite, professional, and helpful tone.
                - ALLOWED LANGUAGES: You may ONLY communicate in English or Tagalog (Filipino).
                - Answer using ONLY the provided facts below. Do not invent or assume any other information.
                - Convert dates to friendly natural language (e.g., 'September 3, 2026').
                - BE FORGIVING: Highly tolerate typos, incorrect spelling (e.g., 'ngayung', 'sked'), bad grammar, and translate Tagalog questions automatically.
                - TIMELINE LOGIC: Even if a term or event has already passed, you MUST answer the question accurately (e.g., 'Term 1 already ended on July 31'). DO NOT ignore questions about past events.

                *** STRICT 'IGNORE' RULES (CRITICAL) ***
                You MUST output exactly the word IGNORE (and nothing else) if the user's message falls into ANY of these categories. By outputting IGNORE, you allow the real human teacher to handle the message personally:
                1. Personal, complex, or specific student concerns (e.g., 'I have a concern about my child', 'Can you check my child's grade?').
                2. Greetings, small talk, or random nonsense without a specific question.
                3. Any language other than English or Tagalog.
                4. Any topic completely unrelated to the school facts provided below.
                5. Passwords or account settings (ONLY the Admin handles this, so ignore it here).
                (CRITICAL EXCEPTION: DO NOT output IGNORE if the user asks about tuition, fees, term dates, calendars, or events. You must answer these).

                *** TEACHER CHEAT SHEET ***
                [PREVIOUS CHAT HISTORY FOR CONTEXT]
                {$historyContext}

                [TUITION & FEES]
                - Tuition is 1,000 PHP per month. Miscellaneous fee is 3,500 PHP.
                - Tuition fee payment schedule: Every second Friday of the month.

                [SCHOOL YEAR & TERMS]
                {$termInfo}
                - Last day of classes (School year ends): {$lastDayOfSchool}.

                [GRADES RELEASE & DEADLINES]
                - Teachers receive an automated system alert exactly 1 week before the end of each term to remind them to finalize grades.
                - Grades are released via the Report Card module 1 to 2 weeks after the end of each Term.

                [UPCOMING CALENDAR EVENTS]
                {$eventsKnowledge}";
            }

            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent?key=' . $apiKey;
            
            $data = [
                "systemInstruction" => ["parts" => [["text" => $systemPrompt]]],
                "contents" => [["parts" => [["text" => $request->message]]]],
                "generationConfig" => [
                    "temperature" => 0.1 // Locks the AI down so it strictly obeys the IGNORE rules
                ],
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); // INCREASED TO 30 SECONDS to prevent silent timeout drops
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $aiText = "IGNORE";
            
            // NEW: Catch specific API throttling and timeout errors so they print to the screen!
            if ($response === false) {
                $aiText = "API ERROR: Request timed out. Google's servers took too long to respond.";
            } elseif ($httpCode === 429) {
                $aiText = "API ERROR: Google API Rate Limit Reached. You are sending messages too fast! Please wait a minute.";
            } else {
                $responseData = json_decode($response);
                
                if (isset($responseData->candidates[0]->content->parts[0]->text)) {
                    $aiText = trim($responseData->candidates[0]->content->parts[0]->text);
                } elseif (isset($responseData->error)) {
                    $aiText = "API ERROR: " . $responseData->error->message;
                }
            }

            $finalAiCheck = trim(strtoupper($aiText));

            if ($finalAiCheck === 'IGNORE') {
                // Do absolutely nothing
            } else {
                $aiResponded = true; // Mark that AI responded

                Message::create([
                    'sender_id' => $request->receiver_id,
                    'receiver_id' => Auth::id(),          
                    'content' => "🤖 AI Assistant: " . $aiText,
                    'is_read' => false,
                ]);
            }
        }

        // PATCHED: Correctly handle JSON responses for dynamic send flow
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'current_msg' => $currentMessage, // Send back created message data
                'ai_responded' => $aiResponded,   // Tell frontend if AI answered
            ]);
        }
        
        return redirect()->route('messages.show', ['id' => $request->receiver_id]);
    }

    private function getAllowedContacts($authUser)
    {
        $users = User::where('user_id', '!=', $authUser->user_id)
            ->whereNull('custom_name') // Don't pull virtual group chat "users" into the direct message list
            ->where('status', 'active') // Only show active users
            ->where(function ($query) use ($authUser) {
                
                // ADMIN: Can message absolutely everyone
                if ($authUser->role === 'admin') {
                    $query->whereNotNull('user_id');
                }
                
                // TEACHER: Can message Admins, ALL Teachers, and ALL Parents
                elseif ($authUser->role === 'teacher') {
                    $query->whereIn('role', ['admin', 'teacher', 'parent']);
                }
                
                elseif ($authUser->role === 'parent') {
                    // Start by allowing Admins and all other Parents
                    $query->whereIn('role', ['admin', 'parent']);
                    
                    // FIX: Pull the section_id directly from the linked student profile 
                    $currentSectionId = $authUser->student ? $authUser->student->section_id : $authUser->section_id;
                    
                    $mySection = \App\Models\Section::where('section_id', $currentSectionId)->first();
                    $myTeacherId = $mySection ? $mySection->teacher_id : null;
                    
                    if ($myTeacherId) {
                        $query->orWhere('user_id', $myTeacherId);
                    }
                }
            })
            ->get();
            
        // Sort the final list alphabetically by name
        return $users->sortBy(function($user) {
            return $user->name;
        })->values();
    }

    private function getArchivedGroups($authId)
    {
        // Now checks the pivot table for personal archives!
        $groupUserIds = DB::table('group_members')->where('user_id', $authId)->where('is_archived', true)->pluck('group_id')->toArray();
        if (empty($groupUserIds)) return collect();
        
        return User::whereIn('user_id', $groupUserIds)->get();
    }

    private function getChatHistoryUsers($authId)
    {
        $individualChats = User::where('user_id', '!=', $authId)->whereNull('custom_name')
            ->where(function ($query) use ($authId) {
                $query->whereHas('sentMessages', function ($q) use ($authId) { $q->where('receiver_id', $authId); })
                      ->orWhereHas('receivedMessages', function ($q) use ($authId) { $q->where('sender_id', $authId); });
            })->get();

        // Only fetch groups where the user has NOT archived them
        $groupUserIds = DB::table('group_members')->where('user_id', $authId)->where('is_archived', false)->pluck('group_id')->toArray();

        $groupChats = collect();
        if (!empty($groupUserIds)) {
            $groupChats = User::whereIn('user_id', $groupUserIds)->get();
        }

        return $individualChats->merge($groupChats)->sortBy(function($user) {
            return $user->custom_name ?? $user->name;
        })->values();

    }

    // 👇 ========================================== 👇
    // PATCHED METHODS FOR DYNAMIC SYNC & DB SEEN STATUS
    // 👇 ========================================== 👇

    /**
     * AJAX Endpoint: Dynamically update DB 'is_read' status for a specific conversation.
     * Tells the DB that the logged-in user has seen messages sent FROM the other person.
     */
    public function markMessagesRead($conversationId)
    {
        $authId = Auth::id();
        
        // Find strictly 1-on-1 messages sent FROM the other person TO me that are still unread.
        $messagesToUpdate = Message::where('sender_id', $conversationId)
                                    ->where('receiver_id', $authId)
                                    ->where('is_read', false);

        if ($messagesToUpdate->count() > 0) {
            $messagesToUpdate->update(['is_read' => true]);
            return response()->json(['success' => true]);
        }

        // NOTE: Group chat dynamic seen is complex (needs per-user tracking table), so we stick to whispers UI-only seen there.

        return response()->json(['success' => false, 'message' => 'No messages to update.']);
    }

    /**
     * AJAX Endpoint: Poll for any new unread messages since the page loaded.
     */
    public function pollForMessages($conversationId)
    {
        $authId = Auth::id();
        
        // Find the other person (strict 1-on-1)
        $receiver = User::find($conversationId);
        if (!$receiver) return response()->json(['messages' => [], 'last_sent_read' => false]);

        // Are we polling a strict 1-on-1 conversation?
        $isGroup = !is_null($receiver->custom_name);

        if ($isGroup) {
            // Fetch messages sent TO this group since initial load
            $newMessages = Message::where('receiver_id', $conversationId)
                                    ->where('sender_id', '!=', $authId) // Don't pull my own sent messages
                                    ->where('created_at', '>', now()->subSeconds(30)) 
                                    ->orderBy('created_at', 'asc')->get();
        } else {
            // Fetch strict 1-on-1 messages sent FROM them TO me, specifically unread.
            $newMessages = Message::where('sender_id', $conversationId)
                                    ->where('receiver_id', $authId)
                                    ->where('is_read', false) 
                                    ->orderBy('created_at', 'asc')->get();
        }

        // Map the results for frontend rendering
        $formattedMessages = $newMessages->map(function($msg) use ($isGroup) {
            return [
                'id' => $msg->id,
                'content' => $msg->content,
                'sender_id' => $msg->sender_id,
                'time_formatted' => $msg->created_at->setTimezone('Asia/Manila')->format('g:i A'),
            ];
        });

        // 👇 NEW: Check if the last message I sent has been read by them!
        $lastSentMessageRead = false;
        if (!$isGroup) {
            $lastMsg = Message::where('sender_id', $authId)
                              ->where('receiver_id', $conversationId)
                              ->orderBy('id', 'desc')
                              ->first();
            
            if ($lastMsg && $lastMsg->is_read) {
                $lastSentMessageRead = true;
            }
        }

        // Return structured JSON with both the messages AND the seen status
        return response()->json([
            'messages' => $formattedMessages,
            'last_sent_read' => $lastSentMessageRead
        ]);
    }
}