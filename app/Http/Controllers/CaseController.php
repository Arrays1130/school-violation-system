<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CaseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\StudentCase::class, 'case');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\StudentCase::with(['student', 'violation'])
            ->forUser($request->user())
            ->latest('occurred_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('full_name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('section', 'LIKE', "%{$search}%");
                })->orWhereHas('violation', function ($vq) use ($search) {
                    $vq->where('title', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'endorsed') {
                $query->whereNotNull('endorsed_at');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('severity')) {
            $severity = $request->severity;
            if (in_array($severity, ['Minor', 'Major'], true)) {
                $query->whereHas('violation', function ($vq) use ($severity) {
                    $vq->where('severity', $severity);
                });
            }
        }

        if ($request->filled('department')) {
            $query->whereHas('student', function ($sq) use ($request) {
                $sq->where('department', $request->department);
            });
        }

        if ($request->filled('academic_year') && $request->academic_year !== 'All') {
            $query->whereHas('student', function ($sq) use ($request) {
                $sq->where('academic_year', $request->academic_year);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date_to);
        }

        $cases = $query->paginate(15)->appends($request->all());

        // Summary stat cards
        $scopedBaseQuery = \App\Models\StudentCase::query()->forUser($request->user());
        $scopedBaseQuery->getQuery()->orders = []; // keep counts stable (remove accidental ordering)

        $statusCounts = (clone $scopedBaseQuery)->select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $summary = [
            'total'   => $statusCounts->sum(),
            'pending' => $statusCounts['Pending'] ?? 0,
            'hearing' => $statusCounts['Hearing Scheduled'] ?? 0,
            'closed'  => $statusCounts['Closed'] ?? 0,
        ];

        return inertia('Cases/Index', [
            'cases' => $cases,
            'summary' => $summary,
            'departments' => \App\Models\Student::selectRaw('TRIM(department) as department')
                ->whereNotNull('department')
                ->distinct()
                ->orderBy('department')
                ->pluck('department'),
            'academicYears' => \App\Models\Student::whereNotNull('academic_year')
                ->distinct()
                ->orderByDesc('academic_year')
                ->pluck('academic_year'),
            'filters' => request()->only(['search', 'status', 'severity', 'department', 'academic_year', 'date_from', 'date_to']),
        ]);
    }

    public function create(?\App\Models\Student $student = null)
    {
        $this->authorize('create', \App\Models\StudentCase::class);

        // Support both /students/{student}/cases/create  AND  /cases/create?student_id=X
        if (is_null($student) || !$student->exists) {
            $studentId = request('student_id');
            $student = $studentId ? \App\Models\Student::find($studentId) : null;
        }

        $violations = \App\Models\Violation::query()->get();
        // Optimize: Only fetch necessary columns to prevent huge JSON payload that freezes the page
        $students   = \App\Models\Student::select('id', 'full_name', 'department')->orderBy('full_name')->get(); // for picker when no student

        return inertia('Cases/Create', [
            'student' => $student,
            'violations' => $violations,
            'students' => $students
        ]);
    }

    public function store(\App\Http\Requests\StoreCaseRequest $request)
    {
        $data = $request->validated();
        
        // Role-Based Validation
        $violation = \App\Models\Violation::find($data['violation_id']);
        
        // Removed restriction: Admins can now record Major offenses as well.

        $data['created_by'] = auth()->id();
        $data['status'] = 'Pending';

        // 1. Calculate Offense Level + catalog sanction
        $advice = app(\App\Services\OffenseAdviceService::class);
        $offenseLevel = $advice->offenseLevelFor((int) $data['student_id'], (int) $data['violation_id']);
        $data['offense_level'] = $offenseLevel;
        $data['sanction'] = $advice->sanctionFor($violation, $offenseLevel);

        $case = \App\Models\StudentCase::createForStaff($data, auth()->id());

        // Dispatch Real-time Event (Queued)
        event(new \App\Events\ViolationRecorded($case));

        // Dispatch Reverb & Database Notifications (Queued)
        $deptShortcut = $case->student->department_shortcut;
        $notifiableUsers = \App\Models\User::where('role', 'super_admin')
            ->orWhere(function ($query) use ($deptShortcut) {
                if ($deptShortcut) {
                    $query->where('role', 'dean')
                        ->where('department', $deptShortcut);
                }
            })->get();
        \Illuminate\Support\Facades\Notification::send($notifiableUsers, new \App\Notifications\NewViolationCaseNotification($case));

        // Trigger N8n Webhook Asynchronously (Queued)
        \App\Jobs\TriggerN8nWebhook::dispatch('violation_recorded', [
            'case_id' => $case->id,
            'student_db_id' => $case->student->id,
            'student_name' => $case->student->full_name,
            'student_email' => $case->student->email,
            'guardian_email' => $case->student->guardian_email,
            'guardian_contact' => $case->student->guardian_phone,
            'department' => $case->student->department,
            'violation_title' => $violation->title,
            'violation_severity' => $violation->severity,
            'sanction' => $data['sanction'],
        ]);

        // Phased: Minor → student + dept dean; Major → also guardian
        \App\Support\StakeholderNotifier::notifyViolationRecorded($case);

        // --- AUTOMATED ESCALATION LOGIC ---
        if ($violation->severity === 'Minor') {
            $escalationForecast = $advice->minorEscalationForecast((int) $data['student_id']);

            if ($escalationForecast['triggers_escalation_now']) {
                $totalMinors = $escalationForecast['total_minors'];
                $escalationLevel = (int) $escalationForecast['escalation_level'];

                // Get or update the System Generated Major Violation
                $escalationViolation = \App\Models\Violation::updateOrCreate(
                    ['code' => 'SYS-001'],
                    [
                        'title' => 'Major Offense',
                        'category' => 'System Generated',
                        'severity' => 'Major',
                        'default_description' => 'System generated for reaching 3 minor offenses.',
                        'first_offense' => 'Refer to Student Affairs',
                        'second_offense' => 'Refer to Discipline Committee',
                        'third_offense' => 'Dismissal Review',
                    ]
                );

                $escalationSanction = $advice->sanctionFor($escalationViolation, $escalationLevel);

                // Create the Major Case
                $escalatedCase = \App\Models\StudentCase::createForStaff([
                    'student_id' => $data['student_id'],
                    'violation_id' => $escalationViolation->id,
                    'description' => "System automatically generated this Major offense because the student reached {$totalMinors} minor offenses.",
                    'occurred_at' => now(),
                    'offense_level' => $escalationLevel,
                    'sanction' => $escalationSanction,
                ], auth()->id() ?? 1);

                // Dispatch Real-time Event for Escalated Case
                event(new \App\Events\ViolationRecorded($escalatedCase));

                // Dispatch Reverb & Database Notifications for Escalated Case
                \Illuminate\Support\Facades\Notification::send($notifiableUsers, new \App\Notifications\NewViolationCaseNotification($escalatedCase));

                // Trigger N8n Webhook for Escalated Case Asynchronously
                \App\Jobs\TriggerN8nWebhook::dispatch('violation_recorded', [
                    'case_id' => $escalatedCase->id,
                    'student_db_id' => $escalatedCase->student->id,
                    'student_name' => $escalatedCase->student->full_name,
                    'student_email' => $escalatedCase->student->email,
                    'guardian_email' => $escalatedCase->student->guardian_email,
                    'guardian_contact' => $escalatedCase->student->guardian_phone,
                    'department' => $escalatedCase->student->department,
                    'violation_title' => $escalatedCase->violation->title,
                    'violation_severity' => $escalatedCase->violation->severity,
                    'sanction' => $escalatedCase->sanction,
                    'is_escalation' => true,
                ]);

                // Escalated Major → student + guardian + dept dean
                \App\Support\StakeholderNotifier::notifyViolationRecorded($escalatedCase);

                \App\Support\QueueHelper::triggerBackgroundWorker();

                // Redirect to the new escalated case with a special message
                return redirect()->route('cases.show', $escalatedCase)
                    ->with('warning', "Violation recorded successfully. The system automatically generated a Major Offense due to reaching {$totalMinors} minor offenses.");
            }
        }
        // --- END ESCALATION LOGIC ---

        session()->flash('success', 'Violation recorded successfully.');

        \App\Support\QueueHelper::triggerBackgroundWorker();

        if (request()->header('X-Inertia')) {
            return \Inertia\Inertia::location(route('cases.show', $case));
        }

        return redirect()->route('cases.show', $case);
    }

    public function show(\App\Models\StudentCase $case)
    {
        $case->load(['student', 'violation', 'hearings', 'creator', 'actions.user', 'attachments.uploader', 'closedByUser']);

        // Get full offense history for this student
        $allStudentCases = \App\Models\StudentCase::where('student_id', $case->student_id)
            ->with('violation')
            ->latest('occurred_at')
            ->get();

        $offenseHistory = $allStudentCases->filter(fn($c) => $c->id !== $case->id)->values();

        // Count offenses by severity for this student in memory
        $offenseSummary = [
            'total'  => $allStudentCases->count(),
            'minor'  => $allStudentCases->filter(fn($c) => $c->violation?->severity === 'Minor')->count(),
            'major'  => $allStudentCases->filter(fn($c) => $c->violation?->severity === 'Major')->count(),
        ];

        return inertia('Cases/Show', [
            'caseRecord' => $case,
            'offenseHistory' => $offenseHistory,
            'offenseSummary' => $offenseSummary,
            'workflow' => [
                'can_close' => $case->canClose(),
                'can_endorse' => $case->canEndorse(),
                'close_block_reason' => $case->closureBlockReason(),
                'endorse_block_reason' => $case->endorseBlockReason(),
                'needs_osa_action' => $case->isMajorOffense() && ! $case->canEndorseToGrievance(),
            ],
            'auth' => ['user' => auth()->user()]
        ]);
    }

    public function edit(\App\Models\StudentCase $case)
    {
        $case->load(['student', 'violation']);
        $violations = \App\Models\Violation::all();
        return inertia('Cases/Edit', [
            'caseRecord' => $case,
            'violations' => $violations
        ]);
    }

    public function update(\App\Http\Requests\UpdateCaseRequest $request, \App\Models\StudentCase $case)
    {
        $case->update($request->validated());
        return redirect()->route('cases.show', $case)
            ->with('success', 'Case updated successfully.');
    }

    public function destroy(\App\Models\StudentCase $case)
    {
        $case->delete();

        session()->flash('success', 'Violation record moved to trash.');

        if (request()->header('X-Inertia')) {
            return \Inertia\Inertia::location(route('cases.index'));
        }

        return redirect()->route('cases.index');
    }

    /**
     * Display a listing of soft-deleted cases.
     */
    public function trash()
    {
        abort_if(auth()->user()->isDean(), 403);

        $cases = \App\Models\StudentCase::onlyTrashed()
            ->with(['student', 'violation'])
            ->latest('deleted_at')
            ->paginate(15);
            
        return inertia('Cases/Trash', [
            'cases' => $cases,
        ]);
    }

    /**
     * Restore a soft-deleted case.
     */
    public function restore($id)
    {
        $case = \App\Models\StudentCase::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $case);
        $case->restore();

        session()->flash('success', 'Violation record has been successfully restored.');

        if (request()->header('X-Inertia')) {
            return \Inertia\Inertia::location(route('students.show', $case->student_id));
        }

        return redirect()->route('students.show', $case->student_id);
    }

    /**
     * Permanently delete a soft-deleted case.
     */
    public function forceDelete($id)
    {
        $case = \App\Models\StudentCase::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $case);
        $case->forceDelete();

        return redirect()->route('cases.trash')->with('success', 'Violation record has been permanently deleted.');
    }

    /**
     * Mark a case as Closed.
     */
    public function close(\App\Models\StudentCase $case)
    {
        $this->authorize('close', $case);

        if ($reason = $case->closureBlockReason()) {
            return back()->with('error', $reason);
        }

        $case->markClosed(auth()->id());
        $case->load(['student', 'violation']);

        \App\Support\StakeholderNotifier::notifyCaseClosed($case);

        // Trigger Webhook for Case Closed Asynchronously
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
        ]);

        \App\Support\QueueHelper::triggerBackgroundWorker();

        return back()->with('success', 'Case has been officially closed.');
    }
    /**
     * Print individual case report.
     */
    public function print(\App\Models\StudentCase $case)
    {
        $this->authorize('view', $case);

        $case->load(['student', 'violation', 'hearings', 'actions.user']);

        return inertia('Cases/Print', [
            'case' => $case,
        ]);
    }
}
