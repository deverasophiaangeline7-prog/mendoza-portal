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

        // Only mark read for 1-on-1 chats to avoid marking a group chat as read for everyone
        if (is_null($selectedUser->custom_name)) {
            Message::where('sender_id', $id)
                   ->where('receiver_id', $authId)
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
        // 2. AI INTERCEPTOR LOGIC
        // ==========================================
        $apiKey = env('GEMINI_API_KEY');
        
        // FIX: Find the receiver FIRST before checking it in the if-statement
        $receiver = User::find($request->receiver_id);

        // STRICT SENDER CHECK: Bulletproofed with strtolower()
        // STRICT CHECK: Sender is Parent AND Receiver is NOT a Group Chat
        if ($apiKey && strtolower(Auth::user()->role) === 'parent' && $receiver && is_null($receiver->custom_name)) {
            
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

            $receiverRole = $receiver ? ucfirst($receiver->role) : 'Staff';
            $systemPrompt = "You are the automated virtual assistant for Mendoza Academy, Inc.
            IMPORTANT: You are currently responding on behalf of a {$receiverRole} account.
            Guidelines:
            - Maintain a polite, professional, and helpful tone.
            - STRICT LANGUAGE MATCHING: You MUST reply in the exact same language as the user's current question.
            - Use the [PREVIOUS CHAT HISTORY] to understand the context of the user's current question.
            - Convert dates to friendly natural language (e.g., 'September 3, 2026').
            - Answer using ONLY the provided facts below.
            - BE FORGIVING: Highly tolerate typos, incorrect spelling, bad grammar, and very short phrases. If you can reasonably guess what the user is asking about (e.g., 'password', 'tuition', 'grades', 'schedule'), provide the relevant answer.
            - ONLY output the word IGNORE if the message is strictly a greeting (e.g., 'hello', 'hi', 'good morning'), complete random nonsense, or a topic completely unrelated to the school facts provided.
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
            $aiText = "IGNORE";
            
            if (isset($responseData->candidates[0]->content->parts[0]->text)) {
                $aiText = trim($responseData->candidates[0]->content->parts[0]->text);
            } elseif (isset($responseData->error)) {
                $aiText = "API ERROR: " . $responseData->error->message;
            }

            if (strpos($aiText, 'IGNORE') !== false) {
                // Do absolutely nothing
            } else {
                Message::create([
                    'sender_id' => $request->receiver_id,
                    'receiver_id' => Auth::id(),          
                    'content' => "🤖 AI Assistant: " . $aiText,
                    'is_read' => false,
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->route('messages.show', ['id' => $request->receiver_id]);
    }

    private function getAllowedContacts($authUser)
{
    $users = User::where('user_id', '!=', $authUser->user_id)
        ->whereNull('custom_name')
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

} 

    
  
