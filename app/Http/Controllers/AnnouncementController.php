<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    // Show all approved announcements
    public function index()
    {
        $announcements = Announcement::with('poster')
            ->where('status', 'approved')
            ->orderBy('date_posted', 'desc')
            ->get();

        return view('announcements.index', compact('announcements'));
    }

    // Show form to create announcement (admin only)
    public function create()
    {
        return view('announcements.create');
    }

    // Save new announcement
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

    // Show edit form (admin only)
    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', compact('announcement'));
    }

    // Save edited announcement
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

    // Archive an announcement (admin only)
    public function archive(Announcement $announcement)
    {
        $announcement->update(['status' => 'archived']);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement archived successfully!');
    }
}