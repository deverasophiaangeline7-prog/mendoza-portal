<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    // Store a new section from the modal form
    public function store(Request $request)
    {
        $request->validate([
            'grade_level' => 'required|string|max:255',
            'section_name' => 'required|string|max:255',
        ]);

        Section::create([
            'grade_level' => $request->grade_level,
            'section_name' => $request->section_name,
        ]);

        return redirect()->back()->with('success', 'Section added successfully!');
    }

    // Delete a section using section_id
    public function destroy(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,section_id',
        ]);

        $section = Section::where('section_id', $request->section_id)->firstOrFail();
        $section->delete();

        return redirect()->back()->with('success', 'Section deleted successfully!');
    }
}
