<?php

namespace App\Http\Controllers;

use App\Models\MeetingMinute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingMinuteController extends Controller
{
    public function index()
    {
        $minutes = MeetingMinute::with('creator')->orderBy('meeting_date', 'desc')->paginate(10);
        return view('meeting-minutes.index', compact('minutes'));
    }

    public function create()
    {
        return view('meeting-minutes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'case_id' => 'nullable|exists:cases,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meeting_date' => 'required|date',
            'venue' => 'required|string|max:255',
        ]);

        $validated['created_by'] = Auth::id();

        MeetingMinute::create($validated);

        return redirect()->route('meeting-minutes.index')->with('success', 'Meeting minutes recorded successfully.');
    }

    public function show(MeetingMinute $meetingMinute)
    {
        $meetingMinute->load(['case.student', 'creator']);
        return view('meeting-minutes.show', compact('meetingMinute'));
    }

    public function edit(MeetingMinute $meetingMinute)
    {
        return view('meeting-minutes.edit', compact('meetingMinute'));
    }

    public function update(Request $request, MeetingMinute $meetingMinute)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meeting_date' => 'required|date',
            'venue' => 'required|string|max:255',
        ]);

        $meetingMinute->update($validated);

        return redirect()->route('meeting-minutes.index')->with('success', 'Meeting minutes updated successfully.');
    }

    public function destroy(MeetingMinute $meetingMinute)
    {
        $meetingMinute->delete();
        return redirect()->route('meeting-minutes.index')->with('success', 'Meeting minutes deleted successfully.');
    }
}
