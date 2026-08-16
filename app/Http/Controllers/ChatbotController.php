<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        // 4. Build the data array containing our Logic/Rules and the user's message
        $data = [
            "system_instruction" => [
                "parts" => [
                    ["text" => "You are the official AI assistant for the Mendoza Academy, Inc. School Monitoring System. Your only job is to answer simple FAQs regarding school fees, class schedules, and official announcements. Rules: 1. Be polite, concise, and helpful. 2. Do not use markdown formatting; provide plain text answers. 3. If a user asks a complex question, a question about their specific grades, or anything outside of fees, schedules, and announcements, you must NOT attempt to answer it. 4. If rule 3 is triggered, you must reply with exactly one word and nothing else: ESCALATE."]
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