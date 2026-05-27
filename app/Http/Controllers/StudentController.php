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

        $students = $query->withCount('cases')
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
        $summary = [
            'total'       => \App\Models\Student::count(),
            'with_cases'  => \App\Models\Student::has('cases')->count(),
            'departments' => $departments->count(),
            'clean'       => \App\Models\Student::doesntHave('cases')->count(),
        ];

        return view('students.index', compact('students', 'departments', 'summary'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'section' => 'required|string|max:255',
            'year_level' => 'required|string|max:255',
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
        
        return view('students.show', compact('student', 'offenseSummary'));
    }

    public function edit(\App\Models\Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, \App\Models\Student $student)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'section' => 'required|string|max:255',
            'year_level' => 'required|string|max:255',
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
}
