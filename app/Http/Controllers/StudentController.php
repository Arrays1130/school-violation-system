<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\StudentsImport;
use App\Mail\CustomMessage;
use App\Services\StudentImporter;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Support\YearLevel;
use App\Support\SchoolSettings;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Student::class, 'student');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Student::query()->forUser($request->user());

        // Simple search - just check if search exists
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('section', 'LIKE', "%{$search}%")
                  ->orWhere('year_level', 'LIKE', "%{$search}%");
            });
        }

        // Department filter with TRIM
        if ($request->has('department') && !empty($request->department)) {
            $query->whereRaw('TRIM(department) = ?', [trim($request->department)]);
        }

        // Year Level filter (accepts canonical labels and legacy numeric values)
        if ($request->has('yearLevel') && !empty($request->yearLevel)) {
            $aliases = YearLevel::aliasesFor($request->yearLevel);
            if ($aliases !== []) {
                $query->whereIn('year_level', $aliases);
            }
        }

        // Academic Year filter
        if ($request->has('academicYear') && !empty($request->academicYear) && $request->academicYear !== 'All') {
            $query->where('academic_year', $request->academicYear);
        }

        $students = $query->withCount([
            'cases',
            'cases as minor_cases_count' => function ($query) {
                $query->whereHas('violation', function ($q) {
                    $q->where('severity', 'Minor');
                });
            },
            'cases as major_cases_count' => function ($query) {
                $query->whereHas('violation', function ($q) {
                    $q->where('severity', '!=', 'Minor');
                });
            }
        ])
            ->orderBy('year_level', 'asc')
            ->orderBy('section', 'asc')
            ->orderBy('full_name', 'asc')
            ->paginate(15)
            ->appends($request->all());

        $scopedBase = \App\Models\Student::query()->forUser($request->user());

        $departments = (clone $scopedBase)
            ->selectRaw('TRIM(department) as department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department')
            ->filter()
            ->values();

        $totalStudents = (clone $scopedBase)->count();
        $withCases = (clone $scopedBase)->has('cases')->count();

        $summary = [
            'total'       => $totalStudents,
            'with_cases'  => $withCases,
            'departments' => $departments->count(),
            'clean'       => $totalStudents - $withCases,
        ];

        $filterAcademicYears = (clone $scopedBase)
            ->select('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year')
            ->filter()
            ->values();

        return \Inertia\Inertia::render('Students/Index', [
            'students' => $students,
            'departments' => $departments,
            'filterAcademicYears' => $filterAcademicYears,
            'summary' => $summary,
            'filters' => request()->all('search', 'department', 'yearLevel', 'academicYear')
        ]);
    }

    public function create()
    {
        $currentAcademicYear = \App\Models\SystemSetting::where('key', 'current_academic_year')->value('value') ?? 'SY 2024-2025';
        return inertia('Students/Create', compact('currentAcademicYear'));
    }

    public function store(\App\Http\Requests\StoreStudentRequest $request)
    {
        $validated = $request->validated();

        // Avoid a shared/default password across all students.
        // If STUDENT_DEFAULT_PASSWORD is unset, we generate a random password per student.
        $tempPassword = config('school.student_default_password') ?: Str::random(24);
        $validated['password'] = Hash::make($tempPassword);
        $validated['password_changed_at'] = null; // force reset if student auth is enabled

        \App\Models\Student::create($validated);

        return redirect()->route('students.index')->with('success', 'Student added successfully.');
    }

    public function show(\App\Models\Student $student)
    {
        $student->load(['cases.violation', 'cases.actions']);
        
        // Calculate offense summary
        $offenseSummary = [
            'total' => $student->cases->count(),
            'minor' => $student->cases->filter(fn($case) => $case->violation?->severity === 'Minor')->count(),
            'major' => $student->cases->filter(fn($case) => $case->violation?->severity === 'Major')->count(),
        ];
        
        $messageTemplates = \App\Models\MessageTemplate::latest()->get();
        
        return inertia('Students/Show', compact('student', 'offenseSummary', 'messageTemplates'));
    }

    public function edit(\App\Models\Student $student)
    {
        return inertia('Students/Edit', compact('student'));
    }

    public function update(\App\Http\Requests\UpdateStudentRequest $request, \App\Models\Student $student)
    {
        $validated = $request->validated();

        $student->update($validated);

        return redirect()->route('students.show', $student)->with('success', 'Student updated successfully.');
    }

    public function destroy(\App\Models\Student $student)
    {
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Student moved to trash.');
    }

    /**
     * Bulk promote 1st-3rd year students to the next year level
     */
    public function promoteStudents()
    {
        abort_if(auth()->user()->isDean(), 403);

        $students = \App\Models\Student::whereIn('year_level', YearLevel::promotableAliases())->get();
        $count = $students->count();

        if ($count === 0) {
            return redirect()->back()->with('error', 'No students found to promote.');
        }

        foreach ($students as $student) {
            $nextLevel = YearLevel::next($student->year_level);
            if ($nextLevel) {
                $student->update(['year_level' => $nextLevel]);
            }
        }

        return redirect()->route('students.index')->with('success', "Successfully promoted {$count} students to the next year level.");
    }

    /**
     * Bulk graduate and archive 4th-year students
     */
    public function graduateFourthYears(Request $request)
    {
        // Check if user is dean, maybe abort (Deans typically shouldn't do bulk deletes if they can't access trash)
        abort_if(auth()->user()->isDean(), 403);

        $request->validate([
            'academic_year' => 'required|string|max:255'
        ]);

        $fourthYearAliases = YearLevel::fourthYearAliases();
        $students = \App\Models\Student::whereIn('year_level', $fourthYearAliases)->get();
        $count = $students->count();

        if ($count === 0) {
            return redirect()->back()->with('error', 'No 4th-year students found to graduate.');
        }

        $academicYear = $request->input('academic_year');

        // Bulk update the academic_year_graduated directly (doesn't trigger model events individually)
        \App\Models\Student::whereIn('year_level', $fourthYearAliases)->update(['academic_year_graduated' => $academicYear]);
        
        // Bulk delete (soft delete)
        \App\Models\Student::whereIn('year_level', $fourthYearAliases)->delete();

        // Clear dashboard cache and dispatch event once after bulk operation
        \App\Models\StudentCase::clearDashboardCache();
        try { event(new \App\Events\DashboardUpdated('Bulk graduated 4th-year students')); } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Dashboard event dispatch failed after bulk graduation', ['error' => $e->getMessage()]);
        }

        return redirect()->route('students.index')->with('success', "Successfully graduated and archived {$count} 4th-year students for {$academicYear}.");
    }

    /**
     * Display a listing of soft-deleted students.
     */
    public function trash()
    {
        abort_if(auth()->user()->isDean(), 403);

        $students = \App\Models\Student::onlyTrashed()->withCount('cases')->latest()->paginate(15);

        return inertia('Students/Trash', [
            'students' => $students,
        ]);
    }

    /**
     * Restore a soft-deleted student.
     */
    public function restore($id)
    {
        $student = \App\Models\Student::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $student);
        $student->restore();

        return redirect()->route('students.index')->with('success', 'Student and all their records have been successfully restored.');
    }

    /**
     * Permanently delete a soft-deleted student.
     */
    public function forceDelete($id)
    {
        $student = \App\Models\Student::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $student);
        $student->forceDelete();

        return redirect()->route('students.trash')->with('success', 'Student has been permanently deleted.');
    }

    /**
     * Search students with violation history for AJAX popup
     */
    public function searchWithHistory(Request $request)
    {
        $search = $request->get('q', '');
        
        $students = \App\Models\Student::query()
            ->forUser($request->user())
            ->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->withCount('cases')
            ->with(['cases' => function($query) {
                $query->with('violation')->latest()->take(5);
            }])
            ->limit(10)
            ->get();

        return response()->json($students);
    }

    /**
     * Get graduated students by academic year for Admin Dashboard
     */
    public function getGraduatedStudents(Request $request)
    {
        $academicYear = $request->get('academic_year');

        if (!$academicYear) {
            return response()->json([]);
        }

        $query = \App\Models\Student::onlyTrashed()
            ->where('academic_year_graduated', $academicYear);

        $user = $request->user();
        if ($user && $user->isDean()) {
            $deanDept = \App\Support\DepartmentResolver::shortcutToLong($user->department);
            $query->where('department', $deanDept);
        }

        $students = $query->get(['id', 'full_name', 'department', 'section', 'year_level', 'deleted_at', 'academic_year_graduated']);

        return response()->json($students);
    }
    /**
     * Print student violation report
     */
    public function printReport(\App\Models\Student $student)
    {
        $this->authorize('view', $student);

        $student->load(['cases.violation', 'cases.hearing', 'cases.actions']);
        
        return view('students.pdf', compact('student'));
    }

    public function importForm()
    {
        $this->authorize('import', \App\Models\Student::class);

        return inertia('Students/Import');
    }

    public function import(Request $request)
    {
        $this->authorize('import', \App\Models\Student::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        set_time_limit(300);

        try {
            \Illuminate\Support\Facades\Log::info('Starting import process...');

            $importer = new StudentImporter(
                (string) SchoolSettings::get('current_academic_year', 'SY 2024-2025')
            );
            $dispatcher = \App\Models\Student::getEventDispatcher();
            \App\Models\Student::unsetEventDispatcher();

            try {
                if ($extension === 'csv' || $extension === 'txt') {
                    $this->importCsvManually($file->getRealPath(), $importer);
                } else {
                    Excel::import(new StudentsImport($importer), $file);
                }
            } finally {
                if ($dispatcher) {
                    \App\Models\Student::setEventDispatcher($dispatcher);
                }
            }

            $imported = $importer->finish();

            if ($imported === 0) {
                return back()->with('error', 'No students were imported. Check that your file has a header row with Email Address and Department columns, then try again.');
            }

            $skipped = $importer->skippedCount();
            $message = "Successfully imported {$imported} student".($imported === 1 ? '' : 's').'.';
            if ($skipped > 0) {
                $message .= " {$skipped} row".($skipped === 1 ? '' : 's').' skipped (missing email).';
            }

            return redirect()->route('students.index')->with('success', $message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Student Import Failure', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Unable to import students. Please verify the file format and try again.');
        }
    }

    private function importCsvManually(string $path, StudentImporter $importer): void
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \Exception('Cannot open CSV file.');
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);

            return;
        }

        $headerMap = [];
        foreach ($headers as $index => $header) {
            $cleanHeader = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', str_replace(' ', '_', trim((string) $header))));
            if ($cleanHeader !== '') {
                $headerMap[$cleanHeader] = $index;
            }
        }

        try {
            while (($rowArr = fgetcsv($handle)) !== false) {
                if (empty(array_filter($rowArr))) {
                    continue;
                }

                $rowData = [];
                foreach ($headerMap as $key => $index) {
                    if (isset($rowArr[$index])) {
                        $rowData[$key] = trim((string) $rowArr[$index]);
                    }
                }

                $importer->addRow($rowData);
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * AI-draft a guardian message from a selected case.
     */
    public function generateGuardianMessage(Request $request, \App\Models\Student $student)
    {
        $this->authorize('view', $student);
        $this->authorize('use-ai-assistant');

        $validated = $request->validate([
            'case_id' => 'required|integer|exists:cases,id',
        ]);

        $case = \App\Models\StudentCase::query()
            ->where('student_id', $student->id)
            ->forUser($request->user())
            ->with('violation')
            ->findOrFail($validated['case_id']);

        $result = app(\App\Services\AiService::class)->generateGuardianMessage($student, $case);

        return response()->json([
            'message' => $result['message'],
            'mode' => $result['mode'],
        ]);
    }

    /**
     * Send Custom Message (SMS and/or Email)
     */
    public function sendCustomMessage(Request $request, \App\Models\Student $student)
    {
        $this->authorize('view', $student);

        $request->validate([
            'message' => 'required|string|max:1000',
            'delivery_method' => 'required|array',
            'delivery_method.*' => 'in:sms,email'
        ]);

        $methods = $request->input('delivery_method', []);
        $successMessages = [];
        $errorMessages = [];

        // 1. Send SMS
        if (in_array('sms', $methods)) {
            $guardianPhone = $student->guardian_phone;


            if ($guardianPhone) {
                // Ensure number is in +639 format for the SMS gateway
                $cleanPhone = preg_replace('/\D/', '', $guardianPhone);
                if (preg_match('/^09\d{9}$/', $cleanPhone)) {
                    $formattedPhone = '+63' . substr($cleanPhone, 1);
                } else {
                    $formattedPhone = $guardianPhone; // Use as-is if already formatted
                }

                try {
                    $smsUrl = env('SMS_GATEWAY_URL');
                    $smsUser = env('SMS_GATEWAY_USERNAME');
                    $smsPass = env('SMS_GATEWAY_PASSWORD');

                    if (! $smsUrl || ! $smsUser || ! $smsPass) {
                        throw new \Exception('SMS gateway is not configured.');
                    }

                    $response = \Illuminate\Support\Facades\Http::timeout(5)
                        ->withBasicAuth($smsUser, $smsPass)
                        ->post($smsUrl, [
                            'textMessage' => [
                                'text' => $request->message
                            ],
                            'phoneNumbers' => [$formattedPhone]
                        ]);
                    
                    if ($response->successful()) {
                        $successMessages[] = 'SMS sent successfully.';
                    } else {
                        throw new \Exception("Gateway returned status: " . $response->status());
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('SMS Sending Failed: ' . $e->getMessage());
                    $errorMessages[] = 'The SMS could not be sent right now. Please try again in a few minutes, or contact your system administrator if the problem continues.';
                }
            } else {
                $errorMessages[] = "The SMS was not sent because this student has no guardian phone number on file. You can add one by editing the student's profile.";
            }
        }

        // 2. Send Email (SMTP when configured, otherwise Google Apps Script relay)
        if (in_array('email', $methods)) {
            if ($student->guardian_email) {
                try {
                    $subject = 'SVS Notification: Message from School';
                    \App\Support\SchoolMailer::sendMailable(
                        $student->guardian_email,
                        new CustomMessage($subject, $request->message),
                    );
                    $successMessages[] = 'Email sent successfully.';
                } catch (\Exception $e) {
                    Log::error('Email Sending Failed: '.$e->getMessage());
                    $errorMessages[] = 'The email could not be sent right now. Please try again in a few minutes, or contact your system administrator if the problem continues.';
                }
            } else {
                $errorMessages[] = "The email was not sent because this student has no guardian email on file. You can add one by editing the student's profile.";
            }
        }

        if (empty($methods)) {
            return back()->with('error', 'Please select at least one delivery method.');
        }

        if (!empty($errorMessages)) {
            // If there are both success and errors, show a warning, else error
            if (!empty($successMessages)) {
                return back()->with('warning', implode(' ', $successMessages) . ' ' . implode(' ', $errorMessages));
            }
            return back()->with('error', implode(' ', $errorMessages));
        }

        return back()->with('success', implode(' ', $successMessages));
    }
}
