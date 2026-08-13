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
     * Display violations that have cases (drill-down entry point).
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $user = $request->user();

        $casesScope = function ($q) use ($request, $user) {
            $q->forUser($user);
            $this->applyCaseListFilters($q, $request, includeSearch: false);
        };

        $query = \App\Models\Violation::query()
            ->whereHas('cases', $casesScope)
            ->withCount(['cases as cases_count' => $casesScope]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('severity') && in_array($request->severity, ['Minor', 'Major'], true)) {
            $query->where('severity', $request->severity);
        }

        $violations = $query->orderBy('title')->paginate(15)->appends($request->all());

        // Summary stat cards (all scoped cases, not filter-narrowed)
        $scopedBaseQuery = \App\Models\StudentCase::query()->forUser($user);
        $scopedBaseQuery->getQuery()->orders = [];

        $statusCounts = (clone $scopedBaseQuery)->select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $summary = [
            'total'   => $statusCounts->sum(),
            'pending' => $statusCounts['Pending'] ?? 0,
            'hearing' => $statusCounts['Hearing Scheduled'] ?? 0,
            'closed'  => $statusCounts['Closed'] ?? 0,
        ];

        return inertia('Cases/Index', [
            'violations' => $violations,
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
            'filters' => $request->only(['search', 'status', 'severity', 'department', 'academic_year', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Day-case list for a single violation type (no student names).
     */
    public function byViolation(\Illuminate\Http\Request $request, \App\Models\Violation $violation)
    {
        $this->authorize('viewAny', \App\Models\StudentCase::class);

        $dayGroups = $this->buildDayGroupsForViolation($request, $violation);

        $page = max(1, (int) $request->get('page', 1));
        $perPage = 15;
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $dayGroups->forPage($page, $perPage)->values(),
            $dayGroups->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return inertia('Cases/ByViolation', [
            'violation' => $violation,
            'dayGroups' => $paginator,
            'departments' => \App\Models\Student::selectRaw('TRIM(department) as department')
                ->whereNotNull('department')
                ->distinct()
                ->orderBy('department')
                ->pluck('department'),
            'academicYears' => \App\Models\Student::whereNotNull('academic_year')
                ->distinct()
                ->orderByDesc('academic_year')
                ->pluck('academic_year'),
            'filters' => $request->only(['search', 'status', 'severity', 'department', 'academic_year', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Students involved on one day-case for a violation type.
     */
    public function byViolationDay(\Illuminate\Http\Request $request, \App\Models\Violation $violation, string $date)
    {
        $this->authorize('viewAny', \App\Models\StudentCase::class);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            abort(404);
        }

        $dayGroups = $this->buildDayGroupsForViolation($request, $violation);
        $dayGroup = $dayGroups->firstWhere('date', $date);

        if (! $dayGroup) {
            abort(404);
        }

        $query = \App\Models\StudentCase::with([
                'student',
                'violation',
                'attachments:id,case_id,file_name,file_type,file_size,label',
            ])
            ->where('violation_id', $violation->id)
            ->forUser($request->user())
            ->whereDate('occurred_at', $date)
            ->latest('occurred_at');

        $this->applyCaseListFilters($query, $request, includeSearch: true);

        $cases = $query->paginate(15)->appends($request->all());

        return inertia('Cases/ByViolationDay', [
            'violation' => $violation,
            'dayGroup' => $dayGroup,
            'cases' => $cases,
            'filters' => $request->only(['search', 'status', 'severity', 'department', 'academic_year', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Distinct occurrence dates for a violation → display sequence 000-1, 000-2, …
     *
     * @return \Illuminate\Support\Collection<int, array{date: string, student_count: int, sequence: int, sequence_label: string, display_label: string}>
     */
    private function buildDayGroupsForViolation(\Illuminate\Http\Request $request, \App\Models\Violation $violation): \Illuminate\Support\Collection
    {
        $query = \App\Models\StudentCase::query()
            ->where('violation_id', $violation->id)
            ->forUser($request->user())
            ->whereNotNull('occurred_at');

        $this->applyCaseListFilters($query, $request, includeSearch: true);

        $dayExpr = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite'
            ? "date(occurred_at)"
            : 'DATE(occurred_at)';

        $rows = $query
            ->selectRaw("{$dayExpr} as day, COUNT(*) as student_count")
            ->groupBy(\Illuminate\Support\Facades\DB::raw($dayExpr))
            ->orderBy('day')
            ->get();

        return $rows->values()->map(function ($row, $index) use ($violation) {
            $sequence = $index + 1;
            $label = sprintf('000-%d', $sequence);
            $date = \Illuminate\Support\Carbon::parse($row->day)->toDateString();

            return [
                'date' => $date,
                'student_count' => (int) $row->student_count,
                'sequence' => $sequence,
                'sequence_label' => $label,
                'display_label' => trim($violation->title).' '.$label,
            ];
        });
    }

    /**
     * Shared case-list filters (status, dept, year, dates; optional student search).
     */
    private function applyCaseListFilters($query, \Illuminate\Http\Request $request, bool $includeSearch = true): void
    {
        if ($includeSearch && $request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('case_code', 'LIKE', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('full_name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('section', 'LIKE', "%{$search}%");
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
        $studentIds = $data['student_ids'];
        unset($data['student_ids'], $data['student_id']);

        $violation = \App\Models\Violation::findOrFail($data['violation_id']);
        $advice = app(\App\Services\OffenseAdviceService::class);

        $createdCases = [];
        $escalatedCases = [];

        foreach ($studentIds as $studentId) {
            $result = $this->recordCaseForStudent(
                (int) $studentId,
                $violation,
                $advice,
                $data,
                (int) auth()->id()
            );

            $createdCases[] = $result['case'];
            if ($result['escalated']) {
                $escalatedCases[] = $result['escalated'];
            }
        }

        \App\Support\QueueHelper::triggerBackgroundWorker();

        $count = count($createdCases);
        $firstCase = $createdCases[0];

        if (count($escalatedCases) > 0) {
            $message = $count === 1
                ? 'Violation recorded successfully. The system automatically generated a Major Offense and endorsed it to the Grievance Committee.'
                : "{$count} violation cases recorded. ".count($escalatedCases).' student(s) were auto-escalated and endorsed to the Grievance Committee.';

            if ($count === 1) {
                return redirect()->route('cases.show', $escalatedCases[0])->with('warning', $message);
            }

            return redirect()->route('cases.by-violation', $violation)->with('warning', $message);
        }

        $majorCount = collect($createdCases)->filter(fn ($c) => $c->isMajorOffense())->count();
        $success = $count === 1
            ? ($majorCount === 1
                ? 'Major offense recorded and automatically endorsed to the Grievance Committee.'
                : 'Violation recorded successfully.')
            : "{$count} violation cases recorded successfully — each student has their own case code."
                .($majorCount > 0 ? " {$majorCount} major case(s) were auto-endorsed." : '');

        if ($count === 1) {
            if (request()->header('X-Inertia')) {
                return \Inertia\Inertia::location(route('cases.show', $firstCase));
            }

            return redirect()->route('cases.show', $firstCase)->with('success', $success);
        }

        return redirect()->route('cases.by-violation', $violation)->with('success', $success);
    }

    /**
     * Create one case for a student (with notifications + minor escalation).
     *
     * @return array{case: \App\Models\StudentCase, escalated: ?\App\Models\StudentCase}
     */
    private function recordCaseForStudent(
        int $studentId,
        \App\Models\Violation $violation,
        \App\Services\OffenseAdviceService $advice,
        array $sharedData,
        int $createdBy
    ): array {
        $offenseLevel = $advice->offenseLevelFor($studentId, (int) $violation->id);

        $case = \App\Models\StudentCase::createForStaff([
            ...$sharedData,
            'student_id' => $studentId,
            'violation_id' => $violation->id,
            'offense_level' => $offenseLevel,
            'sanction' => $advice->sanctionFor($violation, $offenseLevel),
        ], $createdBy);

        $case->load(['student', 'violation']);

        if ($case->isMajorOffense()) {
            $case->endorseToGrievance(
                $createdBy,
                'Automatically endorsed to the Grievance Committee — major offense.'
            );
        }

        event(new \App\Events\ViolationRecorded($case));

        $deptShortcut = $case->student->department_shortcut;
        $notifiableUsers = \App\Models\User::where('role', 'super_admin')
            ->orWhere(function ($query) use ($deptShortcut) {
                if ($deptShortcut) {
                    $query->where('role', 'dean')
                        ->where('department', $deptShortcut);
                }
            })->get();

        \Illuminate\Support\Facades\Notification::send(
            $notifiableUsers,
            new \App\Notifications\NewViolationCaseNotification($case)
        );

        \App\Jobs\TriggerN8nWebhook::dispatch('violation_recorded', [
            'case_id' => $case->id,
            'case_code' => $case->case_code,
            'student_db_id' => $case->student->id,
            'student_name' => $case->student->full_name,
            'student_email' => $case->student->email,
            'guardian_email' => $case->student->guardian_email,
            'guardian_contact' => $case->student->guardian_phone,
            'department' => $case->student->department,
            'violation_title' => $violation->title,
            'violation_severity' => $violation->severity,
            'sanction' => $case->sanction,
        ]);

        \App\Support\StakeholderNotifier::notifyViolationRecorded($case);

        $escalatedCase = null;

        if ($violation->severity === 'Minor') {
            $escalationForecast = $advice->minorEscalationForecast($studentId);

            if ($escalationForecast['triggers_escalation_now']) {
                $totalMinors = $escalationForecast['total_minors'];
                $escalationLevel = (int) $escalationForecast['escalation_level'];

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

                $escalatedCase = \App\Models\StudentCase::createForStaff([
                    'student_id' => $studentId,
                    'violation_id' => $escalationViolation->id,
                    'description' => "System automatically generated this Major offense because the student reached {$totalMinors} minor offenses.",
                    'occurred_at' => now(),
                    'offense_level' => $escalationLevel,
                    'sanction' => $advice->sanctionFor($escalationViolation, $escalationLevel),
                ], $createdBy ?: 1);

                $escalatedCase->load(['student', 'violation']);
                $escalatedCase->endorseToGrievance(
                    $createdBy ?: 1,
                    'Automatically endorsed to the Grievance Committee — major offense (3-minor escalation).'
                );

                event(new \App\Events\ViolationRecorded($escalatedCase));
                \Illuminate\Support\Facades\Notification::send(
                    $notifiableUsers,
                    new \App\Notifications\NewViolationCaseNotification($escalatedCase)
                );

                \App\Jobs\TriggerN8nWebhook::dispatch('violation_recorded', [
                    'case_id' => $escalatedCase->id,
                    'case_code' => $escalatedCase->case_code,
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

                \App\Support\StakeholderNotifier::notifyViolationRecorded($escalatedCase);
            }
        }

        return ['case' => $case, 'escalated' => $escalatedCase];
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
     * Soft-delete every active student case (move all to Trash Bin).
     */
    public function emptyAll(\Illuminate\Http\Request $request)
    {
        abort_if($request->user()->isDean(), 403);
        abort_unless($request->user()->isAdmin() || $request->user()->isSuperAdmin(), 403);

        $count = 0;
        \App\Models\StudentCase::query()->orderBy('id')->chunkById(100, function ($cases) use (&$count) {
            foreach ($cases as $case) {
                $case->delete();
                $count++;
            }
        });

        \App\Support\DashboardCache::bust();
        \App\Support\MobileCache::bust();

        return redirect()
            ->route('cases.index')
            ->with('success', $count === 0
                ? 'No active student cases to remove.'
                : "Moved {$count} student case(s) to trash.");
    }

    /**
     * Permanently delete every case currently in the Trash Bin.
     */
    public function emptyTrash(\Illuminate\Http\Request $request)
    {
        abort_if($request->user()->isDean(), 403);
        abort_unless($request->user()->isSuperAdmin(), 403);

        $count = 0;
        \App\Models\StudentCase::onlyTrashed()->orderBy('id')->chunkById(100, function ($cases) use (&$count) {
            foreach ($cases as $case) {
                $case->forceDelete();
                $count++;
            }
        });

        \App\Support\DashboardCache::bust();
        \App\Support\MobileCache::bust();

        return redirect()
            ->route('cases.trash')
            ->with('success', $count === 0
                ? 'Trash is already empty.'
                : "Permanently deleted {$count} student case(s).");
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

        $case->load(['student', 'violation', 'hearings', 'creator', 'actions.user']);

        return inertia('Cases/Print', [
            'case' => $case,
        ]);
    }
}
