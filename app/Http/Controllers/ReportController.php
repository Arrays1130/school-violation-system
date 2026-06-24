<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $cases = $this->getFilteredCases($request)->paginate(15);
        $departments = \App\Models\Student::distinct()->pluck('department');
        
        return inertia('Reports/Index', [
            'cases' => $cases,
            'departments' => $departments,
            'filters' => $request->all(),
        ]);
    }

    public function print(Request $request)
    {
        $cases = $this->getFilteredCases($request)->get();
        return view('reports.print', compact('cases'));
    }

    public function pdf(Request $request) 
    {
        $cases = $this->getFilteredCases($request)->get();
        // Uses standard View 'reports.pdf', no sidebar/layout
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', compact('cases'));
        return $pdf->download('violation_report_' . date('Y-m-d') . '.pdf');
    }

    public function csv(Request $request)
    {
        $query = $this->getFilteredCases($request);

        return response()->streamDownload(function () use ($query) {
            $file = fopen('php://output', 'w');
            
            // Write BOM for UTF-8 Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Write CSV headers
            fputcsv($file, [
                'Case ID',
                'Date Occurred',
                'Student ID',
                'Student Name',
                'Department',
                'Violation Title',
                'Severity',
                'Status',
                'Sanction',
                'Offense Level'
            ]);

            // Stream rows query chunks to keep memory usage extremely low
            $query->chunk(200, function ($cases) use ($file) {
                foreach ($cases as $case) {
                    fputcsv($file, [
                        $case->id,
                        $case->occurred_at ? $case->occurred_at->format('Y-m-d H:i') : '',
                        $case->student->student_id ?? 'N/A',
                        $case->student->full_name ?? 'N/A',
                        $case->student->department ?? '',
                        $case->violation->title ?? 'N/A',
                        $case->violation->severity ?? '',
                        $case->status,
                        $case->sanction ?? 'N/A',
                        $case->offense_level ?? 1
                    ]);
                }
            });

            fclose($file);
        }, 'violation_report_' . date('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function retrieval(Request $request)
    {
        $cases = $this->getFilteredCases($request)->paginate(15);
        $departments = \App\Models\Student::distinct()->pluck('department');
        $violations = \App\Models\Violation::all();
        $academicYears = \App\Models\Student::whereNotNull('academic_year')
                            ->distinct()
                            ->pluck('academic_year')
                            ->merge(\App\Models\Student::whereNotNull('academic_year_graduated')->distinct()->pluck('academic_year_graduated'))
                            ->unique()
                            ->sortDesc()
                            ->values();
        
        return inertia('Reports/Retrieval', [
            'cases' => $cases,
            'departments' => $departments,
            'violations' => $violations,
            'academicYears' => $academicYears,
            'filters' => $request->all(),
        ]);
    }

    public function sanctions(Request $request)
    {
        $query = \App\Models\StudentCase::query()
            ->whereHas('student')
            ->with(['student', 'violation', 'closedByUser']);

        // Filters
        if ($request->filled('department')) {
            $query->whereHas('student', fn($q) => $q->where('department', $request->department));
        }
        if ($request->filled('severity')) {
            $query->whereHas('violation', fn($q) => $q->where('severity', $request->severity));
        }
        if ($request->filled('sanction_status')) {
            if ($request->sanction_status === 'served') {
                $query->where('status', 'Closed');
            } elseif ($request->sanction_status === 'pending') {
                $query->where('status', '!=', 'Closed');
            }
        }
        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date_to);
        }

        $cases = $query->latest('occurred_at')->paginate(20)->appends($request->all());

        // Summary counters
        $base = \App\Models\StudentCase::query()->whereHas('student')->whereNotNull('sanction');
        $sanctionStatusCounts = (clone $base)->select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $totalSanctions  = $sanctionStatusCounts->sum();
        $sanctionsServed = $sanctionStatusCounts['Closed'] ?? 0;
        $sanctionsPending = $totalSanctions - $sanctionsServed;
        $complianceRate  = $totalSanctions > 0 ? round(($sanctionsServed / $totalSanctions) * 100) : 0;

        $departments = \App\Models\Student::distinct()->pluck('department');

        return inertia('Reports/Sanctions', [
            'cases' => $cases,
            'departments' => $departments,
            'totalSanctions' => $totalSanctions,
            'sanctionsServed' => $sanctionsServed,
            'sanctionsPending' => $sanctionsPending,
            'complianceRate' => $complianceRate,
            'filters' => $request->all(),
        ]);
    }

    public function system(Request $request)
    {
        $cases = \App\Models\StudentCase::query()->whereHas('student')->with(['student', 'violation']);

        // Overview counters
        $statusCounts = clone($cases)->select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $total      = $statusCounts->sum();
        $pending    = $statusCounts['Pending'] ?? 0;
        $hearing    = $statusCounts['Hearing Scheduled'] ?? 0;
        $endorsed   = $statusCounts['Endorsed to Grievance'] ?? 0;
        $closed     = $statusCounts['Closed'] ?? 0;

        // Department breakdown
        $byDepartment = (clone $cases)
            ->join('students', 'cases.student_id', '=', 'students.id')
            ->selectRaw('students.department, COUNT(*) as total')
            ->groupBy('students.department')
            ->orderByDesc('total')
            ->get();

        // Top violations
        $topViolations = (clone $cases)
            ->join('violations', 'cases.violation_id', '=', 'violations.id')
            ->selectRaw('violations.title, violations.severity, violations.code, COUNT(*) as total')
            ->groupBy('violations.id', 'violations.title', 'violations.severity', 'violations.code')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Monthly trend (current year)
        $currentYear = now()->year;
        $monthlyTrend = (clone $cases)
            ->whereYear('cases.occurred_at', $currentYear)
            ->selectRaw('MONTH(cases.occurred_at) as month, COUNT(*) as total')
            ->groupByRaw('MONTH(cases.occurred_at)')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $monthlyMinorTrend = (clone $cases)
            ->join('violations as minor_violations', 'cases.violation_id', '=', 'minor_violations.id')
            ->whereYear('cases.occurred_at', $currentYear)
            ->where('minor_violations.severity', 'Minor')
            ->selectRaw('MONTH(cases.occurred_at) as month, COUNT(*) as total')
            ->groupByRaw('MONTH(cases.occurred_at)')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $monthlyMajorTrend = (clone $cases)
            ->join('violations as major_violations', 'cases.violation_id', '=', 'major_violations.id')
            ->whereYear('cases.occurred_at', $currentYear)
            ->whereIn('major_violations.severity', ['Major', 'Critical'])
            ->selectRaw('MONTH(cases.occurred_at) as month, COUNT(*) as total')
            ->groupByRaw('MONTH(cases.occurred_at)')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill all 12 months (0 for months with no data)
        $monthlyData = [];
        $monthlyMinorData = [];
        $monthlyMajorData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[$m] = $monthlyTrend[$m] ?? 0;
            $monthlyMinorData[$m] = $monthlyMinorTrend[$m] ?? 0;
            $monthlyMajorData[$m] = $monthlyMajorTrend[$m] ?? 0;
        }

        return inertia('Reports/System', [
            'total' => $total,
            'pending' => $pending,
            'hearing' => $hearing,
            'endorsed' => $endorsed,
            'closed' => $closed,
            'byDepartment' => $byDepartment,
            'topViolations' => $topViolations,
            'monthlyData' => $monthlyData,
            'monthlyMinorData' => $monthlyMinorData,
            'monthlyMajorData' => $monthlyMajorData,
            'currentYear' => $currentYear,
        ]);
    }

    public function bulkAction(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $ids = $request->input('selected_ids', []);
        $action = $request->input('action');
        if (empty($ids) || empty($action)) {
            return redirect()->back()->with('error', 'No cases selected or action specified.');
        }
        switch ($action) {
            case 'mark_reviewed':
                \App\Models\StudentCase::whereIn('id', $ids)->update(['reviewed' => true]);
                break;
            case 'close':
                \App\Models\StudentCase::whereIn('id', $ids)->update(['status' => 'Closed']);
                break;
            default:
                // Add other bulk actions here
                break;
        }
        return redirect()->back()->with('status', 'Bulk action applied successfully.');
    }

    private function getFilteredCases(Request $request)
    {
        $query = \App\Models\StudentCase::query()
            ->forUser($request->user())
            ->whereHas('student')
            ->with(['student', 'violation', 'hearing']);

        if ($request->filled('department')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('severity')) {
            $query->whereHas('violation', function($q) use ($request) {
                $q->where('severity', $request->severity);
            });
        }

        if ($request->filled('violation_id')) {
            $query->where('violation_id', $request->violation_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date_to);
        }

        if ($request->filled('date_month')) {
            // Expect format YYYY-MM (e.g., 2023-07)
            $dateMonth = $request->date_month;
            $year = substr($dateMonth, 0, 4);
            $month = substr($dateMonth, 5, 2);
            $query->whereYear('occurred_at', '=', $year)
                  ->whereMonth('occurred_at', '=', $month);
        }

        if ($request->filled('student_search')) {
            $searchTerm = $request->student_search;
            $query->whereHas('student', function($q) use ($searchTerm) {
                $q->where('full_name', 'like', "%{$searchTerm}%")
                  ->orWhere('student_id', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('academic_year')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('academic_year', $request->academic_year)
                  ->orWhere('academic_year_graduated', $request->academic_year);
            });
        }

        return $query->latest('occurred_at');
    }
}
