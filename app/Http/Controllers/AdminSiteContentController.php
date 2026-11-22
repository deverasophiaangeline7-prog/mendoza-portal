<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteContent;
use App\Models\SiteSetting;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminSiteContentController extends Controller
{
    public function edit()
    {
        if (auth()->user()->role !== 'superadmin') {
            abort(403, 'Unauthorized access. Only superadmins can manage the site.');
        }

        $contents = SiteContent::all()->groupBy('page');
        $settings = SiteSetting::getAll();

        // Curated FontAwesome 6 icons for the icon picker
        $availableIcons = [
            'fa-solid fa-chart-line', 'fa-solid fa-house', 'fa-solid fa-gauge',
            'fa-solid fa-user-shield', 'fa-solid fa-user-graduate', 'fa-solid fa-user-tie',
            'fa-solid fa-chalkboard-user', 'fa-solid fa-calendar-days', 'fa-solid fa-calendar-check',
            'fa-solid fa-star', 'fa-solid fa-award', 'fa-solid fa-file-lines',
            'fa-solid fa-clipboard-user', 'fa-solid fa-user-group', 'fa-solid fa-users-gear',
            'fa-solid fa-bullhorn', 'fa-solid fa-bell', 'fa-solid fa-envelope',
            'fa-solid fa-book', 'fa-solid fa-school', 'fa-solid fa-gear',
            'fa-solid fa-clock', 'fa-solid fa-folder-open', 'fa-solid fa-list-check',
        ];

        return view('admin.sitecontent.edit', compact('contents', 'settings', 'availableIcons'));
    }

    public function update(Request $request)
    {
        if (auth()->user()->role !== 'superadmin') {
            abort(403, 'Unauthorized access. Only superadmins can manage the site.');
        }

        $request->validate([
            'confirm_password' => 'required|string',
        ]);

        if (!Hash::check($request->confirm_password, auth()->user()->password)) {
            return redirect()->back()
                ->withErrors(['confirm_password' => 'Incorrect password. Changes were not saved.'])
                ->withInput();
        }

        $updatedPages = [];
        $updatedSettings = false;

        // 1. Handle Global Site Settings (school_name, colors)
        if ($request->has('settings')) {
            foreach ($request->settings as $key => $value) {
                SiteSetting::setValue($key, $value);
                $updatedSettings = true;
            }
        }

        // 2. Handle Logo & Favicon Uploads
        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('branding', 'public');
            SiteSetting::setValue('logo_path', 'storage/' . $path);
            $updatedSettings = true;
        }

        if ($request->hasFile('favicon_file')) {
            $path = $request->file('favicon_file')->store('branding', 'public');
            SiteSetting::setValue('favicon_path', 'storage/' . $path);
            $updatedSettings = true;
        }

        // 3. Handle Navigation Labels & Icons
        if ($request->has('nav_items')) {
            SiteSetting::setValue('nav_items', $request->nav_items);
            $updatedSettings = true;
        }

        // 4. Existing Homepage Text Fields
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

        // 5. Existing Homepage Image Uploads
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

        // 6. Audit Logging
        if ($updatedSettings) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Update Site Settings',
                'description' => 'Updated global site branding, theme colors, or navigation settings.'
            ]);
        }

        if (!empty($updatedPages)) {
            foreach (array_unique($updatedPages) as $pageName) {
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Update Content',
                    'description' => 'Updated the system content for page: ' . ucfirst($pageName)
                ]);
            }
        }

        return redirect()->back()->with('status', 'Site settings updated successfully!');
    }

    public function reset()
    {
        if (auth()->user()->role !== 'superadmin') {
            abort(403, 'Unauthorized access.');
        }

        SiteSetting::resetToDefaults();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Reset Site Settings',
            'description' => 'Reset site branding, colors, and navigation back to default values.'
        ]);

        return redirect()->back()->with('status', 'Site settings restored to default!');
    }
}