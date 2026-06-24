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
        return inertia('Minutes/Index', [
            'minutes' => $minutes
        ]);
    }

    public function create()
    {
        $cases = \App\Models\StudentCase::with('student', 'violation')->latest()->get();
        return inertia('Minutes/Create', [
            'cases' => $cases
        ]);
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
        return inertia('Minutes/Show', [
            'meetingMinute' => $meetingMinute
        ]);
    }

    public function edit(MeetingMinute $meetingMinute)
    {
        return inertia('Minutes/Edit', [
            'meetingMinute' => $meetingMinute
        ]);
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
