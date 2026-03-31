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
    public function archive(AnnouncementImage $announcementImage)
    {
        $announcementImage->update(['status' => 'archived']);

        return redirect()->back()->with('success', 'Image archived successfully!');
    }
}