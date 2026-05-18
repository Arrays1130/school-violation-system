<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\DashboardUpdated;

class HearingController extends Controller
{
    public function create(\App\Models\StudentCase $case)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admins can schedule hearings.');
        }
        
        return view('hearings.create', compact('case'));
    }

    public function store(\App\Http\Requests\StoreHearingRequest $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admins can schedule hearings.');
        }
        
        $data = $request->validated();
        
        $hearingData = [
            'case_id' => $data['case_id'],
            'venue' => $data['venue'],
            'scheduled_at' => $data['scheduled_at'],
            'participants' => isset($data['participants']) ? array_map('trim', explode(',', $data['participants'])) : ['Student', 'Dean of Discipline'],
            'notes' => $data['notes'] ?? null,
            'meeting_minutes' => $data['meeting_minutes'] ?? null,
        ];

        $hearing = \App\Models\Hearing::create($hearingData);

        // Update case status to Hearing Scheduled
        $hearing->case->update(['status' => 'Hearing Scheduled']);

        // Trigger N8n Webhook Asynchronously
        \App\Jobs\TriggerN8nWebhook::dispatch('hearing_scheduled', [
            'hearing_id' => $hearing->id,
            'case_id' => $hearing->case->id,
            'student_id' => $hearing->case->student->student_id,
            'student_name' => collect([$hearing->case->student->first_name, $hearing->case->student->middle_name, $hearing->case->student->last_name])->filter()->join(' '),
            'student_email' => $hearing->case->student->email,
            'guardian_email' => $hearing->case->student->guardian_email,
            'guardian_contact' => $hearing->case->student->guardian_contact,
            'department' => $hearing->case->student->department,
            'venue' => $hearing->venue,
            'scheduled_at' => \Carbon\Carbon::parse($hearing->scheduled_at)->format('F j, Y g:i A'),
            'violation_title' => $hearing->case->violation->title,
        ]);

        // Send Notification to Student (Email + In-app)
        try {
            if ($hearing->case->student->email) {
                $hearing->case->student->notify(new \App\Notifications\HearingScheduled($hearing));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to notify student about hearing: ' . $e->getMessage());
        }

        // Notify ALL Deans about the hearing
        try {
            $allDeans = \App\Models\User::where('role', 'dean')->get();
            foreach ($allDeans as $dean) {
                $dean->notify(new \App\Notifications\DeanHearingNotification($hearing));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to notify all Deans about hearing: ' . $e->getMessage());
        }

        // Broadcast the update for real-time dashboards
        try { event(new DashboardUpdated('Hearing scheduled')); } catch (\Exception $e) {}

        return redirect()->route('students.show', $hearing->case->student_id)
            ->with('success', 'Hearing scheduled successfully.');
    }

    public function show(\App\Models\Hearing $hearing)
    {
        return view('hearings.show', compact('hearing'));
    }

    public function edit(\App\Models\Hearing $hearing)
    {
        return view('hearings.edit', compact('hearing'));
    }

    public function update(\App\Http\Requests\UpdateHearingRequest $request, \App\Models\Hearing $hearing)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admins can update hearings.');
        }
        
        $data = $request->validated();
        
        $hearing->update([
            'venue' => $data['location'],
            'scheduled_at' => \Carbon\Carbon::parse($data['scheduled_date'] . ' ' . $data['scheduled_time']),
            'notes' => $data['notes'] ?? null,
            'meeting_minutes' => $data['meeting_minutes'] ?? null,
        ]);

        // Send Notification to Student (Email + In-app)
        try {
            if ($hearing->case->student->email) {
                $hearing->case->student->notify(new \App\Notifications\HearingScheduled($hearing));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to notify student about hearing update: ' . $e->getMessage());
        }

        // Broadcast the update for real-time dashboards
        try { event(new DashboardUpdated('Hearing updated')); } catch (\Exception $e) {}

        return redirect()->route('students.show', $hearing->case->student_id)
            ->with('success', 'Hearing updated successfully.');
    }

    public function destroy(\App\Models\Hearing $hearing)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admins can delete hearings.');
        }
        
        $studentId = $hearing->case->student_id;
        $hearing->delete();

        // Broadcast the update for real-time dashboards
        try { event(new DashboardUpdated('Hearing deleted')); } catch (\Exception $e) {}

        return redirect()->route('students.show', $studentId)
            ->with('success', 'Hearing deleted successfully.');
    }

    public function markCompleted(Request $request, \App\Models\Hearing $hearing)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admins can mark hearings as complete.');
        }

        $request->validate([
            'sanction' => 'required|string',
        ]);

        // Update the case status, sanction, and closing info
        $hearing->case->update([
            'status' => 'Closed',
            'sanction' => $request->sanction,
            'closed_at' => now(),
            'closed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Hearing marked as completed and case closed with sanction.');
    }

    public function start(\App\Models\Hearing $hearing)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admins can start hearings.');
        }
        
        $hearing->case->update(['status' => 'Hearing']);
        return back()->with('success', 'Hearing has officially started.');
    }

    public function printMom(\App\Models\Hearing $hearing)
    {
        return view('hearings.print_mom', compact('hearing'));
    }
}
