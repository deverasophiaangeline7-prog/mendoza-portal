<?php

namespace App\Http\Controllers;

use App\Models\SchoolCalendar;
use Illuminate\Http\Request;

class SchoolCalendarController extends Controller
{
    // Get all active events (used by the calendar)
    public function index()
    {
        $events = SchoolCalendar::where('status', 'active')
            ->orderBy('start_date', 'asc')
            ->get();

        return view('calendar.index', compact('events'));
    }

    // Show form to create event (admin only)
    public function create()
    {
        return view('calendar.create');
    }

    // Save new event
    public function store(Request $request)
    {
        $request->validate([
            'event_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'event_type'  => 'required|in:holiday,exam,activity,meeting,other',
        ]);

        SchoolCalendar::create([
            'event_title' => $request->event_title,
            'description' => $request->description,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'event_type'  => $request->event_type,
            'is_global'   => 1,
            'posted_by'   => auth()->id(),
            'status'      => 'active',
        ]);

        return redirect()->route('calendar.index')
            ->with('success', 'Event added successfully!');
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

    // Archive an event (admin only)
    public function archive(SchoolCalendar $schoolCalendar)
    {
        $schoolCalendar->update(['status' => 'archived']);

        return redirect()->route('calendar.index')
            ->with('success', 'Event archived successfully!');
    }
}