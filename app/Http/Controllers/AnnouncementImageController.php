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
    public function fetchActive()
    {
        $images = \App\Models\AnnouncementImage::where('status', 'active')->latest()->get();
        
        if ($images->count() > 0) {
            // Format the images into an array for Alpine to easily read
            $formatted = $images->map(function($img) {
                return [
                    'url' => asset('storage/' . $img->image_path),
                    'caption' => $img->caption
                ];
            });
            
            return response()->json([
                'has_image' => true, 
                'images' => $formatted
            ]);
        }
        
        return response()->json(['has_image' => false, 'images' => []]);
    }
}