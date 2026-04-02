<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
}