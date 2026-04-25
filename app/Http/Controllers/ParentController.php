<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolCalendar;
use App\Models\AnnouncementImage; // Import your model

class ParentController extends Controller
{
    public function index()
    {
        // 1. Get only the active announcements
        // 2. Order by newest first
        $announcementImages = AnnouncementImage::where('is_archived', false)
                                ->orderBy('created_at', 'desc')
                                ->get();

        // 3. Send the data to the parent dashboard view
        return view('parent.dashboard', compact('announcementImages'));
    }
    public function viewCalendar()
    {
        // Get the ID of the logged-in student
        $studentId = auth()->user()->student_id;

        // Fetch only events where this student is participating
        $events = SchoolCalendar::whereHas('participants', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })
        ->with(['participants' => function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        }])
        ->orderBy('start_date', 'asc')
        ->get();

        return view('parent.my-calendar', compact('events'));
    }
}