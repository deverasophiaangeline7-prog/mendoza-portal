<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KnowledgeBase;

class KnowledgeBaseController extends Controller
{
    public function index()
    {
        $facts = KnowledgeBase::orderBy('created_at', 'desc')->get();
        return view('admin.knowledge-base', compact('facts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'information' => 'required|string',
        ]);

        KnowledgeBase::create([
            'topic' => $request->topic,
            'information' => $request->information,
            'is_active' => true,
        ]);
        
        return back()->with('success', 'New AI knowledge added successfully!');
    }

    public function destroy($id)
    {
        KnowledgeBase::findOrFail($id)->delete();
        return back()->with('success', 'Fact removed from AI knowledge base.');
    }
}