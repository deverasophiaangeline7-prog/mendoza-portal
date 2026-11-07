<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteContent;
use Illuminate\Support\Facades\Storage;

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
        // Handle text fields
        if ($request->has('content')) {
            foreach ($request->content as $page => $items) {
                foreach ($items as $key => $value) {
                    SiteContent::updateOrCreate(
                        ['page' => $page, 'key' => $key],
                        ['value' => $value]
                    );
                }
            }
        }

        // Handle image file uploads (sliders, backgrounds)
        if ($request->hasFile('images')) {
            foreach ($request->images as $page => $files) {
                foreach ($files as $key => $file) {
                    $path = $file->store('images', 'public');
                    SiteContent::updateOrCreate(
                        ['page' => $page, 'key' => $key],
                        ['value' => $path]
                    );
                }
            }
        }

        return redirect()->back()->with('status', 'Website content updated successfully!');
    }
}
