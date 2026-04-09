<?php

namespace App\Http\Controllers;

use App\Models\SchoolCalendar;
use Illuminate\Http\Request;

class SchoolCalendarController extends Controller
{
    // Get all active events (used by the calendar)
    public function index()
{
    // 1. Fetch the events from the DB
    $dbEvents = \App\Models\SchoolCalendar::all();

    // 2. Prepare the array for Alpine.js
    $eventsData = [];
    foreach ($dbEvents as $event) {
        $eventsData[$event->start_date] = [
            'name' => $event->event_title,
            'time' => $event->time,
            'ps'   => $event->description,
        ];
    }

    // 3. Fetch your announcements (keep your existing logic here)
    $announcementImages = \App\Models\AnnouncementImage::where('status', 'active')->get();

    // 4. Pass EVERYTHING to the view
    return view('dashboard', compact('eventsData', 'announcementImages'));
}

    // Save new event
    public function store(Request $request)
{
    // 1. Validate only the fields we kept
    $request->validate([
        'event_title' => 'required|string|max:255',
        'time' => 'nullable|string',
        'description' => 'nullable|string',
        'start_date'  => 'required|date',
    ]);

    // 2. Use updateOrCreate to prevent duplicate rows for the same date
    \App\Models\SchoolCalendar::updateOrCreate(
        ['start_date' => $request->start_date],
        [
            'event_title' => $request->event_title,
            'description' => $request->description,
            'time'        => $request->time,
        ]
    );

    // 3. Return a JSON response for Alpine.js
    return response()->json(['message' => 'Event saved!']);
}

    // Show edit form (admin only)
    public function edit(SchoolCalendar $schoolCalendar)
    {
        return view('calendar.edit', compact('schoolCalendar'));
    }

    // Save edited event
    public function update(Request $request, SchoolCalendar $schoolCalendar)
    {
        $request->validate([
            'event_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'event_type'  => 'required|in:holiday,exam,activity,meeting,other',
        ]);

        $schoolCalendar->update([
            'event_title' => $request->event_title,
            'description' => $request->description,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'event_type'  => $request->event_type,
        ]);

        return redirect()->route('calendar.index')
            ->with('success', 'Event updated successfully!');
    }

    public function destroy($date)
{
    // Find the event by the date string sent from the frontend
    $event = \App\Models\SchoolCalendar::where('start_date', $date)->first();

    if ($event) {
        $event->delete();
        return response()->json(['success' => true]);
    }

    return response()->json(['error' => 'Event not found'], 404);
}
}