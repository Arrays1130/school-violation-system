<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        // Year Level filter
        if ($request->has('yearLevel') && !empty($request->yearLevel)) {
            $query->where('year_level', $request->yearLevel);
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
        
        // Get departments - trimmed and sorted
        $departments = \App\Models\Student::selectRaw('TRIM(department) as department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department')
            ->filter()
            ->values();
        // Summary stat cards
        $totalStudents = \App\Models\Student::count();
        $withCases = \App\Models\Student::has('cases')->count();

        $summary = [
            'total'       => $totalStudents,
            'with_cases'  => $withCases,
            'departments' => $departments->count(),
            'clean'       => $totalStudents - $withCases,
        ];

        // Get academic years for filtering
        $filterAcademicYears = \App\Models\Student::select('academic_year')
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
        return view('students.create', compact('currentAcademicYear'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:students,email',
            'section' => 'required|string|max:255',
            'year_level' => 'required|string|max:255',
            'academic_year' => 'nullable|string|max:255',
            'department' => 'required|string|max:255',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_email' => 'nullable|email',
            'guardian_phone' => 'nullable|string|max:20',
        ]);

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
        
        return view('students.show', compact('student', 'offenseSummary', 'messageTemplates'));
    }

    public function edit(\App\Models\Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, \App\Models\Student $student)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'section' => 'required|string|max:255',
            'year_level' => 'required|string|max:255',
            'academic_year' => 'nullable|string|max:255',
            'department' => 'required|string|max:255',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_email' => 'nullable|email',
            'guardian_phone' => 'nullable|string|max:20',
        ]);

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

        $students = \App\Models\Student::whereIn('year_level', ['1st Year', '2nd Year', '3rd Year'])->get();
        $count = $students->count();

        if ($count === 0) {
            return redirect()->back()->with('error', 'No students found to promote.');
        }

        foreach ($students as $student) {
            if ($student->year_level === '3rd Year') {
                $student->update(['year_level' => '4th Year']);
            } elseif ($student->year_level === '2nd Year') {
                $student->update(['year_level' => '3rd Year']);
            } elseif ($student->year_level === '1st Year') {
                $student->update(['year_level' => '2nd Year']);
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

        $students = \App\Models\Student::where('year_level', '4th Year')->get();
        $count = $students->count();

        if ($count === 0) {
            return redirect()->back()->with('error', 'No 4th-year students found to graduate.');
        }

        $academicYear = $request->input('academic_year');

        // Bulk update the academic_year_graduated directly (doesn't trigger model events individually)
        \App\Models\Student::where('year_level', '4th Year')->update(['academic_year_graduated' => $academicYear]);
        
        // Bulk delete (soft delete)
        \App\Models\Student::where('year_level', '4th Year')->delete();

        // Clear dashboard cache and dispatch event once after bulk operation
        \App\Models\StudentCase::clearDashboardCache();
        try { event(new \App\Events\DashboardUpdated('Bulk graduated 4th-year students')); } catch (\Exception $e) {}

        return redirect()->route('students.index')->with('success', "Successfully graduated and archived {$count} 4th-year students for {$academicYear}.");
    }

    /**
     * Display a listing of soft-deleted students.
     */
    public function trash()
    {
        abort_if(auth()->user()->isDean(), 403);

        $students = \App\Models\Student::onlyTrashed()->withCount('cases')->latest()->paginate(15);
        return view('students.trash', compact('students'));
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

        $students = \App\Models\Student::onlyTrashed()
            ->where('academic_year_graduated', $academicYear)
            ->get(['id', 'full_name', 'department', 'section', 'year_level', 'deleted_at', 'academic_year_graduated']);

        return response()->json($students);
    }
    /**
     * Print student violation report
     */
    public function printReport(\App\Models\Student $student)
    {
        $student->load(['cases.violation', 'cases.hearing', 'cases.actions']);
        
        return view('students.pdf', compact('student'));
    }

    public function importForm()
    {
        $this->authorize('import', \App\Models\Student::class);

        return view('students.import');
    }

    public function import(Request $request)
    {
        $this->authorize('import', \App\Models\Student::class);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            \Illuminate\Support\Facades\Log::info('Starting import process...');
            Excel::import(new StudentsImport, $request->file('file'));
            return redirect()->route('students.index')->with('success', 'Students imported successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Student Import Failure: ' . $e->getMessage());
            return back()->with('error', 'Error importing students: ' . $e->getMessage());
        }
    }

    /**
     * Send Custom Message (SMS and/or Email)
     */
    public function sendCustomMessage(Request $request, \App\Models\Student $student)
    {
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
                    $response = \Illuminate\Support\Facades\Http::timeout(5)
                        ->withBasicAuth(env('SMS_GATEWAY_USERNAME', 'IG8TFT'), env('SMS_GATEWAY_PASSWORD', 'q4lzeljjwx--al'))
                        ->post(env('SMS_GATEWAY_URL', 'https://api.sms-gate.app/3rdparty/v1/message'), [
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
                    $errorMessages[] = 'Failed to send SMS (Gateway unreachable/Error). Please check if your SMS Gateway app is running and the IP address is correct.';
                }
            } else {
                $errorMessages[] = 'No valid guardian phone number found for SMS.';
            }
        }

        // 2. Send Email
        if (in_array('email', $methods)) {
            if ($student->guardian_email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($student->guardian_email)
                        ->send(new \App\Mail\CustomMessage('SVS Notification: Message from School', $request->message));
                    $successMessages[] = 'Email sent successfully.';
                } catch (\Exception $e) {
                    $errorMessages[] = 'Failed to send Email. Check SMTP settings.';
                }
            } else {
                $errorMessages[] = 'No guardian email found.';
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
