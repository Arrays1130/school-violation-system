<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $cases = $this->getFilteredCases($request)->paginate(15);
        $departments = \App\Models\Student::distinct()->pluck('department');
        
        return view('reports.index', compact('cases', 'departments'));
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
        
        return view('reports.retrieval', compact('cases', 'departments', 'violations'));
    }

    private function getFilteredCases(Request $request)
    {
        $query = \App\Models\StudentCase::query()
            ->whereHas('student') // Ensure we only get cases for non-deleted students
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

        if ($request->filled('student_search')) {
            $searchTerm = $request->student_search;
            $query->whereHas('student', function($q) use ($searchTerm) {
                $q->where('full_name', 'like', "%{$searchTerm}%")
                  ->orWhere('student_id', 'like', "%{$searchTerm}%");
            });
        }

        return $query->latest('occurred_at');
    }
}
