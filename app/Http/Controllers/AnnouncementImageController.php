<?php

namespace App\Http\Controllers;

use App\Models\AnnouncementImage;
use Illuminate\Http\Request;

class AnnouncementImageController extends Controller
{
    // Save uploaded image (admin only)
    public function store(Request $request)
    {
        $request->validate([
            'image'   => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'caption' => 'nullable|string|max:255',
        ]);

        $path = $request->file('image')->store('announcement_images', 'public');

        AnnouncementImage::create([
            'posted_by'  => auth()->id(),
            'image_path' => $path,
            'caption'    => $request->caption,
            'status'     => 'active',
        ]);

        return redirect()->back()->with('success', 'Image uploaded successfully!');
    }

    // Archive an image (admin only)
    public function archive($announcementImage) 
    {
        // Using findOrFail ensures we find the record using your 'image_id'
        $image = AnnouncementImage::findOrFail($announcementImage);
        $image->update(['status' => 'archived']);

        return redirect()->back()->with('success', 'Image archived successfully!');
    }

    // NEW: View the list of archived images
    public function archivedIndex()
    {
        $archivedImages = AnnouncementImage::where('status', 'archived')->latest()->get();
        return view('announcements.archived', compact('archivedImages'));
    }

    // Restore an image
    public function restore($announcementImage)
    {
        $image = AnnouncementImage::findOrFail($announcementImage);
        $image->update(['status' => 'active']);

        return redirect()->route('dashboard')->with('success', 'Image restored!');
    }
}