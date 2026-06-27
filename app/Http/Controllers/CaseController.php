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
            $query->where('status', $request->status);
        }

        if ($request->filled('severity')) {
            $severity = $request->severity;
            $query->whereHas('violation', function ($vq) use ($severity) {
                $vq->where('severity', $severity);
            });
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
            'filters' => request()->only(['search', 'status', 'severity'])
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

        // 1. Calculate Offense Level
        $previousCount = \App\Models\StudentCase::where('student_id', $data['student_id'])
                            ->where('violation_id', $data['violation_id'])
                            ->count();
        $offenseLevel = $previousCount + 1;
        $data['offense_level'] = $offenseLevel;

        // 2. Determine Sanction based on level
        $violation = \App\Models\Violation::find($data['violation_id']);
        $sanction = match($offenseLevel) {
            1 => $violation->first_offense,
            2 => $violation->second_offense,
            3 => $violation->third_offense,
            default => 'Recurring Offense (Refer to Student Affairs)',
        };

        // Fallback or explicit "null" handling if needed
        $data['sanction'] = $sanction ?: 'Sanction pending determination.';

        $case = \App\Models\StudentCase::create($data);
        
        // --- SEND SMS TO GUARDIAN VIA ANDROID GATEWAY ---
        $guardianPhone = $case->student->guardian_phone;
        

        if ($guardianPhone) {
            try {
                $smsMessage = "SVS Notice: Your student {$case->student->full_name} has a recorded violation: {$violation->title}. Sanction: {$data['sanction']}. Please contact the school.";
                
                \Illuminate\Support\Facades\Http::withBasicAuth(env('SMS_GATEWAY_USERNAME', 'IG8TFT'), env('SMS_GATEWAY_PASSWORD', 'q4lzeljjwx--al'))
                    ->post(env('SMS_GATEWAY_URL', 'https://api.sms-gate.app/3rdparty/v1/message'), [
                        'textMessage' => [
                            'text' => $smsMessage
                        ],
                        'phoneNumbers' => [$guardianPhone]
                    ]);
            } catch (\Exception $e) {
                // Ignore errors (Halimbawa: naka-off ang WiFi ng phone para hindi mag-crash ang system)
            }
        }
        // ------------------------------------------------
        
        // Dispatch Real-time Event
        event(new \App\Events\ViolationRecorded($case));

        // Dispatch Reverb & Database Notifications
        $notifiableUsers = \App\Models\User::where('role', 'super_admin')
            ->orWhere(function($query) use ($case) {
                if ($case->student->department) {
                    $query->where('role', 'dean')
                          ->where('department', $case->student->department);
                }
            })->get();
        \Illuminate\Support\Facades\Notification::send($notifiableUsers, new \App\Notifications\NewViolationCaseNotification($case));

        // Trigger N8n Webhook Asynchronously
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

        // --- AUTOMATED ESCALATION LOGIC ---
        // 1. Check if the newly added case is a Minor offense
        if ($violation->severity === 'Minor') {
            // 2. Count total Minor offenses for this student
            $totalMinors = \App\Models\StudentCase::where('student_id', $data['student_id'])
                ->whereHas('violation', function ($q) {
                    $q->where('severity', 'Minor');
                })->count();

            // 3. If total minors is a multiple of 3 (e.g., 3, 6, 9...)
            if ($totalMinors > 0 && $totalMinors % 3 === 0) {
                
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

                // Calculate how many times they've escalated (e.g. 6 minors = 2 escalations)
                $escalationLevel = $totalMinors / 3;

                $escalationSanction = match($escalationLevel) {
                    1 => $escalationViolation->first_offense,
                    2 => $escalationViolation->second_offense,
                    3 => $escalationViolation->third_offense,
                    default => 'Severe Recurring Offense (Refer to Discipline Committee)',
                };

                // Create the Major Case
                $escalatedCase = \App\Models\StudentCase::create([
                    'student_id' => $data['student_id'],
                    'violation_id' => $escalationViolation->id,
                    'description' => "System automatically generated this Major offense because the student reached {$totalMinors} minor offenses.",
                    'occurred_at' => now(),
                    'status' => 'Pending',
                    'created_by' => auth()->id() ?? 1, // Fallback to 1 if system runs it
                    'offense_level' => $escalationLevel,
                    'sanction' => $escalationSanction,
                ]);

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

                // Notify for the escalated case (Student)
                if ($escalatedCase->student->email) {
                    try {
                        $escalatedCase->student->notify(new \App\Notifications\ViolationRecorded($escalatedCase));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send escalation notification (Student): " . $e->getMessage());
                    }
                }

                // Notify Deans for the escalated case
                try {
                    $deans = \App\Models\User::where('role', 'dean')
                        ->where('department', $escalatedCase->student->department_shortcut)
                        ->get();
                    
                    foreach ($deans as $dean) {
                        $dean->notify(new \App\Notifications\DeanViolationNotification($escalatedCase));
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to notify Deans about escalation: " . $e->getMessage());
                }

                // Redirect to the new escalated case with a special message
                return redirect()->route('cases.show', $escalatedCase)
                    ->with('warning', "Violation recorded successfully. The system automatically generated a Major Offense due to reaching {$totalMinors} minor offenses.");
            }
        }
        // --- END ESCALATION LOGIC ---

        // Send Notification to Student (Email + In-app)
        if ($case->student->email) {
            try {
                $case->student->notify(new \App\Notifications\ViolationRecorded($case));
            } catch (\Exception $e) {
                // Log but don't crash
                \Illuminate\Support\Facades\Log::error("Failed to send notification: " . $e->getMessage());
            }
        }

        // Also send to guardian email if available
        if ($case->student->guardian_email) {
            try {
                \Illuminate\Support\Facades\Notification::route('mail', $case->student->guardian_email)
                    ->notify(new \App\Notifications\ViolationRecorded($case));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send guardian notification: " . $e->getMessage());
            }
        }

        // Send to Deans of the department
        try {
            $deans = \App\Models\User::where('role', 'dean')
                ->where('department', $case->student->department_shortcut)
                ->get();
            
            foreach ($deans as $dean) {
                $dean->notify(new \App\Notifications\DeanViolationNotification($case));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to notify Deans: " . $e->getMessage());
        }

        session()->flash('success', 'Violation recorded successfully.');

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
            'major'  => $allStudentCases->filter(fn($c) => in_array($c->violation?->severity, ['Major', 'Critical']))->count(),
        ];

        return inertia('Cases/Show', [
            'caseRecord' => $case,
            'offenseHistory' => $offenseHistory,
            'offenseSummary' => $offenseSummary,
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
        $studentId = $case->student_id;
        $case->delete();
        return redirect()->route('students.show', $studentId)
            ->with('success', 'Violation record moved to trash.');
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
            
        return view('cases.trash', compact('cases'));
    }

    /**
     * Restore a soft-deleted case.
     */
    public function restore($id)
    {
        $case = \App\Models\StudentCase::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $case);
        $case->restore();

        return redirect()->route('students.show', $case->student_id)
            ->with('success', 'Violation record has been successfully restored.');
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

        $case->update([
            'status'    => 'Closed',
            'closed_at' => now(),
            'closed_by' => auth()->id(),
        ]);

        // Trigger N8n Webhook for Case Closed Asynchronously
        \App\Jobs\TriggerN8nWebhook::dispatch('case_closed', [
            'case_id' => $case->id,
            'student_db_id' => $case->student->id,
            'student_name' => $case->student->full_name,
            'student_email' => $case->student->email,
            'guardian_contact' => $case->student->guardian_phone,
            'violation_title' => $case->violation->title,
            'sanction' => $case->sanction,
            'closed_at' => $case->closed_at->toIso8601String(),
        ]);

        return back()->with('success', 'Case has been officially closed.');
    }
    /**
     * Print individual case report.
     */
    public function print(\App\Models\StudentCase $case)
    {
        $case->load(['student', 'violation', 'hearings', 'actions.user']);
        return view('cases.print', compact('case'));
    }
}
