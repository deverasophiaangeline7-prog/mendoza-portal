<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\ClassroomAnnouncement;
use Illuminate\Support\Facades\Notification;

class ClassroomAnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255'
        ]);

        $advisorySectionIds = auth()->user()->sections->pluck('section_id');

        $parents = User::whereHas('student', function ($query) use ($advisorySectionIds) {
            $query->whereIn('section_id', $advisorySectionIds);
        })->get();

        if ($parents->isEmpty()) {
            return back()->with('error', 'No parents found for your advisory class.');
        }

        Notification::send($parents, new ClassroomAnnouncement($request->message));

        return back()->with('success', 'Announcement sent to all advisory parents!');
    }
}
