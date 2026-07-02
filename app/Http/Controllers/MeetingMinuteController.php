<?php

namespace App\Http\Controllers;

use App\Models\MeetingMinute;
use App\Models\StudentCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingMinuteController extends Controller
{
    public function create(Request $request)
    {
        $this->authorize('create', StudentCase::class);

        $casesQuery = StudentCase::with('student', 'violation')->latest();
        if ($request->user()->isDean()) {
            $casesQuery->forUser($request->user());
        }

        return inertia('Minutes/Create', [
            'cases' => $casesQuery->get(),
        ]);
    }

    public function store(\App\Http\Requests\StoreMeetingMinuteRequest $request)
    {
        $validated = $request->validated();

        $this->authorizeMinuteMutation($validated['case_id'] ?? null);

        $validated['created_by'] = Auth::id();

        MeetingMinute::create($validated);

        return redirect()->route('meeting-minutes.index')->with('success', 'Meeting minutes recorded successfully.');
    }

    public function show(MeetingMinute $meetingMinute)
    {
        $meetingMinute->load(['case.student', 'creator']);
        $this->authorizeMinuteView($meetingMinute);

        return inertia('Minutes/Show', [
            'meetingMinute' => $meetingMinute,
        ]);
    }

    public function edit(MeetingMinute $meetingMinute)
    {
        $this->authorizeMinuteMutation($meetingMinute->case_id);

        return inertia('Minutes/Edit', [
            'meetingMinute' => $meetingMinute,
        ]);
    }

    public function update(\App\Http\Requests\UpdateMeetingMinuteRequest $request, MeetingMinute $meetingMinute)
    {
        $this->authorizeMinuteMutation($meetingMinute->case_id);

        $validated = $request->validated();

        $meetingMinute->update($validated);

        return redirect()->route('meeting-minutes.index')->with('success', 'Meeting minutes updated successfully.');
    }

    public function destroy(MeetingMinute $meetingMinute)
    {
        $this->authorizeMinuteMutation($meetingMinute->case_id);

        $meetingMinute->delete();

        return redirect()->route('meeting-minutes.index')->with('success', 'Meeting minutes deleted successfully.');
    }

    protected function authorizeMinuteView(MeetingMinute $meetingMinute): void
    {
        if ($meetingMinute->case_id) {
            $this->authorize('view', $meetingMinute->case);

            return;
        }

        $this->authorize('create', StudentCase::class);
    }

    protected function authorizeMinuteMutation(?int $caseId): void
    {
        if ($caseId) {
            $case = StudentCase::findOrFail($caseId);
            $this->authorize('update', $case);

            return;
        }

        $this->authorize('create', StudentCase::class);
    }
}
