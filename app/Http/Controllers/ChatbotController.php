<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolYear;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    public function handleChat(Request $request)
    {
        // 1. Get the message sent from your frontend
        $userMessage = $request->input('message');
        
        // 2. Grab your secret API key from the .env file
        $apiKey = env('GEMINI_API_KEY'); 
        
        // 3. Set the Gemini API endpoint URL
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

        // Fetch the active school year
        $activeYear = SchoolYear::where('status', 'active')->first();

        // Format the term schedule into a readable string for Gemini
        $termContext = "No active term schedule set.";
        if ($activeYear) {
            $termContext = "Current Academic Term Schedule: " .
                "Term 1: " . Carbon::parse($activeYear->term1_start)->format('M j, Y') . " to " . Carbon::parse($activeYear->term1_end)->format('M j, Y') . ". " .
                "Term 2: " . Carbon::parse($activeYear->term2_start)->format('M j, Y') . " to " . Carbon::parse($activeYear->term2_end)->format('M j, Y') . ". " .
                "Term 3: " . Carbon::parse($activeYear->term3_start)->format('M j, Y') . " to " . Carbon::parse($activeYear->term3_end)->format('M j, Y') . ". " .
                "Note: Grading sheets automatically lock at 11:59 PM on the term's end date.";
        }

        // Build the system instruction combining your rules and the dynamic term schedule
        $systemPrompt = "You are the official AI assistant for the Mendoza Academy, Inc. School Monitoring System. Your only job is to answer simple FAQs regarding school fees, class schedules, and official announcements. " .
                        "Rules: 1. Be polite, concise, and helpful. 2. Do not use markdown formatting; provide plain text answers. 3. If a user asks a complex question, a question about their specific grades, or anything outside of fees, schedules, grading deadlines, and announcements, you must NOT attempt to answer it. 4. If rule 3 is triggered, you must reply with exactly one word and nothing else: ESCALATE. " .
                        $termContext;

        // 4. Build the data array containing our Logic/Rules and the user's message
        $data = [
            "system_instruction" => [
                "parts" => [
                    ["text" => $systemPrompt]
                ]
            ],
            "contents" => [
                ["parts" => [["text" => $userMessage]]]
            ]
        ];

        // 5. Use native PHP cURL to send the hardcoded HTTP request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        
        if(curl_errno($ch)){
            return response()->json(['error' => 'cURL Error: ' . curl_error($ch)], 500);
        }
        
        curl_close($ch);

        // 6. Return the AI's response back to your frontend JavaScript
        return response()->json(json_decode($response));
    }
}