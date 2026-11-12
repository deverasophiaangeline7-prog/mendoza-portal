<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteContent;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AdminSiteContentController extends Controller
{
    public function edit()
    {
        // Group content by page so it's easy to loop through in the view
        $contents = SiteContent::all()->groupBy('page');
        return view('admin.sitecontent.edit', compact('contents'));
    }

    public function update(Request $request)
    {
        $updatedPages = [];

        // Handle text fields
        if ($request->has('content')) {
            foreach ($request->content as $page => $items) {
                foreach ($items as $key => $value) {
                    $record = SiteContent::updateOrCreate(
                        ['page' => $page, 'key' => $key],
                        ['value' => $value]
                    );

                    if ($record->wasChanged() || $record->wasRecentlyCreated) {
                            $updatedPages[] = $page;
                        }
                }
            }
        }

        // Handle image file uploads (sliders, backgrounds)
        if ($request->hasFile('images')) {
            foreach ($request->images as $page => $files) {
                foreach ($files as $key => $file) {
                    $path = $file->store('images', 'public');
                    $record = SiteContent::updateOrCreate(
                        ['page' => $page, 'key' => $key],
                        ['value' => $path]
                    );

                    if ($record->wasChanged() || $record->wasRecentlyCreated) {
                        $updatedPages[] = $page;
                    }
                }
            }
        }

        // --- ACTIVITY LOGGING SCRIPT ---
    if (!empty($updatedPages)) {
        $uniquePages = array_unique($updatedPages);
        
        foreach ($uniquePages as $pageName) {
        AuditLog::create([
            'user_id' => Auth::id(), 
            'action' => 'Update Content',
            'description' => "Updated the system content for page: " . ucfirst($pageName)
        ]);
    }

        return redirect()->back()->with('status', 'Website content updated successfully!');
    }
}
}