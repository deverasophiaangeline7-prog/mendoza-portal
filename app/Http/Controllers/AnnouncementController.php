<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    // Show all announcements (for parents and teachers to read)
    public function index()
    {
        $announcements = Announcement::with('poster')
            ->orderBy('date_posted', 'desc')
            ->get();

        return view('announcements.index', compact('announcements'));
    }

    // Show the form to create an announcement
    public function create()
    {
        return view('announcements.create');
    }

    // Save the new announcement
    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'scope'   => 'required|in:classroom,school-wide',
        ]);

        Announcement::create([
            'posted_by'   => auth()->id(),
            'title'       => $request->title,
            'content'     => $request->content,
            'scope'       => $request->scope,
            'status'      => 'approved',
            'date_posted' => now(),
        ]);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement posted successfully!');
    }

    // Show the edit form
    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', compact('announcement'));
    }

    // Save the edited announcement
    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'scope'   => 'required|in:classroom,school-wide',
        ]);

        $announcement->update([
            'title'   => $request->title,
            'content' => $request->content,
            'scope'   => $request->scope,
        ]);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    // Delete an announcement
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement deleted successfully!');
    }
}