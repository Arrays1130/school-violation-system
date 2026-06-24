<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function index()
    {
        $templates = MessageTemplate::latest()->paginate(10);
        return inertia('MessageTemplates/Index', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
        ]);

        MessageTemplate::create($validated);
        return back()->with('success', 'Message template created successfully.');
    }

    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
        ]);

        $messageTemplate->update($validated);
        return back()->with('success', 'Message template updated successfully.');
    }

    public function destroy(MessageTemplate $messageTemplate)
    {
        $messageTemplate->delete();
        return back()->with('success', 'Message template deleted successfully.');
    }
}
