<?php

namespace App\Http\Controllers;

use App\Events\DashboardUpdated;
use App\Models\Hearing;
use App\Models\StudentCase;
use Illuminate\Http\Request;

class HearingController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Hearing::class, 'hearing');
    }

    public function create(StudentCase $case)
    {
        $this->authorize('create', Hearing::class);
        $case->load('student', 'violation');

        return inertia('Hearings/Create', [
            'studentCase' => $case,
        ]);
    }

    public function store(\App\Http\Requests\StoreHearingRequest $request)
    {
        $data = $request->validated();

        $hearingData = [
            'case_id' => $data['case_id'],
            'venue' => $data['venue'],
            'scheduled_at' => $data['scheduled_at'],
            'participants' => isset($data['participants']) ? array_map('trim', explode(',', $data['participants'])) : ['Student', 'Dean of Discipline'],
            'notes' => $data['notes'] ?? null,
            'meeting_minutes' => $data['meeting_minutes'] ?? null,
        ];

        $hearing = Hearing::create($hearingData);
        $hearing->load(['case.student', 'case.violation']);

        $hearing->case->transitionStatus('Hearing Scheduled');

        \App\Jobs\TriggerN8nWebhook::dispatch('hearing_scheduled', [
            'hearing_id' => $hearing->id,
            'case_id' => $hearing->case->id,
            'student_id' => $hearing->case->student->student_id,
            'student_name' => $hearing->case->student->full_name,
            'student_email' => $hearing->case->student->email,
            'guardian_email' => $hearing->case->student->guardian_email,
            'guardian_contact' => $hearing->case->student->guardian_phone,
            'department' => $hearing->case->student->department,
            'venue' => $hearing->venue,
            'scheduled_at' => \Carbon\Carbon::parse($hearing->scheduled_at)->format('F j, Y g:i A'),
            'violation_title' => $hearing->case->violation->title,
        ]);

        \App\Support\StakeholderNotifier::notifyHearingScheduled($hearing);

        try {
            event(new DashboardUpdated('Hearing scheduled'));
        } catch (\Exception $e) {
        }

        \App\Support\QueueHelper::triggerBackgroundWorker();

        session()->flash('success', 'Hearing scheduled successfully.');

        if (request()->header('X-Inertia')) {
            return \Inertia\Inertia::location(route('students.show', $hearing->case->student_id));
        }

        return redirect()->route('students.show', $hearing->case->student_id);
    }

    public function show(Hearing $hearing)
    {
        $hearing->load('case.student', 'case.violation');
        return inertia('Hearings/Show', [
            'hearing' => $hearing
        ]);
    }

    public function edit(Hearing $hearing)
    {
        $hearing->load('case.student', 'case.violation');
        return inertia('Hearings/Edit', [
            'hearing' => $hearing
        ]);
    }

    public function update(\App\Http\Requests\UpdateHearingRequest $request, Hearing $hearing)
    {
        $data = $request->validated();

        $hearing->update([
            'venue' => $data['location'],
            'scheduled_at' => \Carbon\Carbon::parse($data['scheduled_date'].' '.$data['scheduled_time']),
            'notes' => $data['notes'] ?? null,
            'meeting_minutes' => $data['meeting_minutes'] ?? null,
        ]);

        $hearing->load(['case.student', 'case.violation']);
        \App\Support\StakeholderNotifier::notifyHearingScheduled($hearing, isUpdate: true);

        try {
            event(new DashboardUpdated('Hearing updated'));
        } catch (\Exception $e) {
            \Log::warning('Dashboard event dispatch failed after hearing update', ['error' => $e->getMessage()]);
        }

        \App\Support\QueueHelper::triggerBackgroundWorker();

        session()->flash('success', 'Hearing updated successfully.');

        if (request()->header('X-Inertia')) {
            return \Inertia\Inertia::location(route('students.show', $hearing->case->student_id));
        }

        return redirect()->route('students.show', $hearing->case->student_id);
    }

    public function destroy(Hearing $hearing)
    {
        $case = $hearing->case;
        $studentId = $case->student_id;
        $hearing->delete();

        if ($case->status !== 'Closed') {
            $hasOtherHearings = $case->hearings()->exists();

            if (! $hasOtherHearings && in_array($case->status, ['Hearing Scheduled', 'Hearing'], true)) {
                $case->transitionStatus('Pending');
            } elseif ($hasOtherHearings && $case->status === 'Hearing') {
                $case->transitionStatus('Hearing Scheduled');
            }
        }

        try {
            event(new DashboardUpdated('Hearing deleted'));
        } catch (\Exception $e) {
            \Log::warning('Dashboard event dispatch failed after hearing delete', ['error' => $e->getMessage()]);
        }

        session()->flash('success', 'Hearing deleted successfully.');

        if (request()->header('X-Inertia')) {
            return \Inertia\Inertia::location(route('students.show', $studentId));
        }

        return redirect()->route('students.show', $studentId);
    }

    public function markCompleted(Request $request, Hearing $hearing)
    {
        $this->authorize('complete', $hearing);

        $request->validate([
            'sanction' => 'required|string',
        ]);

        $case = $hearing->case;
        $case->update(['sanction' => $request->sanction]);
        $case->markClosed(auth()->id());
        $case->load(['student', 'violation']);

        \App\Support\StakeholderNotifier::notifyCaseClosed($case);

        \App\Jobs\TriggerN8nWebhook::dispatch('case_closed', [
            'case_id' => $case->id,
            'student_db_id' => $case->student->id,
            'student_name' => $case->student->full_name,
            'student_email' => $case->student->email,
            'guardian_email' => $case->student->guardian_email,
            'guardian_contact' => $case->student->guardian_phone,
            'violation_title' => $case->violation->title,
            'sanction' => $case->sanction,
            'closed_at' => $case->closed_at->toIso8601String(),
            'via_hearing' => true,
        ]);

        \App\Support\QueueHelper::triggerBackgroundWorker();

        return back()->with('success', 'Hearing marked as completed and case closed with sanction.');
    }

    public function start(Hearing $hearing)
    {
        $this->authorize('start', $hearing);

        $hearing->case->transitionStatus('Hearing');

        \App\Support\StakeholderNotifier::notifyAllDeansHearingStarted($hearing);

        \App\Support\QueueHelper::triggerBackgroundWorker();

        return back()->with('success', 'Hearing has officially started.');
    }

    public function printMom(Hearing $hearing)
    {
        $this->authorize('view', $hearing);

        return view('hearings.print_mom', compact('hearing'));
    }

    public function calendar(Request $request)
    {
        $this->authorize('viewAny', Hearing::class);

        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $start = \Carbon\Carbon::parse($month.'-01')->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $query = Hearing::query()
            ->with(['case.student', 'case.violation'])
            ->whereBetween('scheduled_at', [$start, $end])
            ->orderBy('scheduled_at');

        if ($request->user()->isDean() && ! $request->user()->isSuperAdmin()) {
            $longName = \App\Support\DepartmentResolver::shortcutToLong($request->user()->department);
            $query->whereHas('case.student', fn ($q) => $q->whereRaw('TRIM(department) = ?', [trim((string) $longName)]));
        }

        $events = $query->get()->map(fn (Hearing $hearing) => [
            'id' => $hearing->id,
            'case_id' => $hearing->case_id,
            'scheduled_at' => $hearing->scheduled_at?->toIso8601String(),
            'venue' => $hearing->venue,
            'student_name' => $hearing->case?->student?->full_name,
            'violation_title' => $hearing->case?->violation?->title,
            'status' => $hearing->case?->status,
        ]);

        return inertia('Hearings/Calendar', [
            'month' => $month,
            'events' => $events,
        ]);
    }
}
