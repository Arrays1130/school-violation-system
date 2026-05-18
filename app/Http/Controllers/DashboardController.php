<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->isDean()) {
            return redirect()->route('dean.dashboard');
        }

        // Helper to apply role-based filtering
        $applyFilter = function ($query) {
            // Both Super Admin and Admin (Dean) can see all severities on the dashboard
            // If we want to restrict other roles later, we can add logic here
        };

        // Cache all heavy DB aggregation metrics for lightning-fast capstone dashboard loading
        $cachedData = Cache::remember('dashboard.data', now()->addMinutes(5), function () use ($applyFilter) {
            // Current stats
            $stats = [
                'total_students' => \App\Models\Student::count(),
                'total_cases' => \App\Models\StudentCase::whereHas('student')
                    ->tap($applyFilter)->count(),
                'open_cases' => \App\Models\StudentCase::whereHas('student')
                    ->whereNotIn('status', ['Closed', 'Dismissed'])
                    ->tap($applyFilter)->count(),
                'closed_cases' => \App\Models\StudentCase::whereHas('student')
                    ->where('status', 'Closed')
                    ->tap($applyFilter)->count(),
                'hearings_this_month' => \App\Models\Hearing::whereHas('case.student')
                    ->whereHas('case', $applyFilter) // Filter hearing's case
                    ->whereBetween('scheduled_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            ];

            // Previous month stats for trend calculation
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            
            $previousStats = [
                'total_students' => \App\Models\Student::where('created_at', '<=', $lastMonthEnd)->count(),
                'total_cases' => \App\Models\StudentCase::whereHas('student')
                    ->where('created_at', '<=', $lastMonthEnd)
                    ->tap($applyFilter)->count(),
                'open_cases' => \App\Models\StudentCase::whereHas('student')
                    ->whereNotIn('status', ['Closed', 'Dismissed'])
                    ->where('created_at', '<=', $lastMonthEnd)
                    ->where(function($query) use ($lastMonthEnd) {
                        $query->whereNull('closed_at')
                              ->orWhere('closed_at', '>', $lastMonthEnd);
                    })
                    ->tap($applyFilter)->count(),
                
                'closed_cases' => \App\Models\StudentCase::whereHas('student')
                    ->where('status', 'Closed')
                    ->where('created_at', '<=', $lastMonthEnd)
                    ->where('closed_at', '<=', $lastMonthEnd)
                    ->tap($applyFilter)->count(),
                    
                // hearings
                'hearings_this_month' => \App\Models\Hearing::whereHas('case.student')
                    ->whereHas('case', $applyFilter)
                    ->whereBetween('scheduled_at', [$lastMonthStart, $lastMonthEnd])->count(),
            ];

            // Calculate trends
            $trends = [];
            foreach ($stats as $key => $value) {
                $previous = $previousStats[$key] ?? 0;
                $change = $value - $previous;
                $percentage = $previous > 0 ? round(($change / $previous) * 100, 1) : ($value > 0 ? 100 : 0);
                
                $trends[$key] = [
                    'change' => $change,
                    'percentage' => abs($percentage),
                    'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
                    'isPositive' => $key === 'hearings_this_month' ? $change <= 0 : ($key === 'total_students' ? $change >= 0 : $change <= 0),
                ];
            }

            // Chart Data: Cases per Department
            $casesPerDept = \App\Models\StudentCase::join('students', 'cases.student_id', '=', 'students.id')
                ->whereNull('students.deleted_at')
                ->whereNull('cases.deleted_at') // Explicitly check cases too
                ->tap($applyFilter)
                ->selectRaw('students.department, COUNT(*) as count')
                ->groupBy('students.department')
                ->pluck('count', 'department')
                ->mapWithKeys(function ($count, $department) {
                    $acronyms = [
                        'Bachelor Of Science In Information System' => 'CCE',
                        'Bachelor Of Science In Criminology' => 'CCJE',
                        'Bachelor Of Technical Vocational Teachers Education' => 'CTE',
                        'College Of Business And Accounting Education' => 'CBAE',
                    ];
                    return [$acronyms[$department] ?? $department => $count];
                });
                
            // Chart Data: Cases per Severity
            $casesPerSeverity = \App\Models\StudentCase::join('students', 'cases.student_id', '=', 'students.id')
                ->whereNull('students.deleted_at')
                ->whereNull('cases.deleted_at') 
                ->join('violations', 'cases.violation_id', '=', 'violations.id')
                ->tap($applyFilter)
                ->selectRaw('violations.severity, COUNT(*) as count')
                ->groupBy('violations.severity')
                ->pluck('count', 'severity');

            // Chart Data: Violation Trend (Last 6 Months) - Ensure all 6 months are present
            $rawMonthlyTrend = \App\Models\StudentCase::whereHas('student')
                ->tap($applyFilter)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
                ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
                ->groupBy('month')
                ->pluck('count', 'month');

            $monthlyTrend = collect([]);
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i)->format('Y-m');
                $monthlyTrend->put($month, $rawMonthlyTrend->get($month, 0));
            }

            // Top 5 Most Common Violations by Title
            $topViolations = \App\Models\StudentCase::join('students', 'cases.student_id', '=', 'students.id')
                ->whereNull('students.deleted_at')
                ->join('violations', 'cases.violation_id', '=', 'violations.id')
                ->tap($applyFilter)
                ->selectRaw('violations.title, COUNT(*) as count')
                ->groupBy('violations.title')
                ->orderByDesc('count')
                ->take(5)
                ->get();

            // Table 1: Students with Violations (Top 5)
            $studentsWithViolations = \App\Models\Student::withCount(['cases' => $applyFilter])
                ->having('cases_count', '>', 0)
                ->orderByDesc('cases_count')
                ->take(5)
                ->get();

            // Table 2: Recent Cases
            $recentCases = \App\Models\StudentCase::with(['student', 'violation'])
                ->whereHas('student')
                ->tap($applyFilter)
                ->latest('occurred_at')
                ->take(5)
                ->get();

            return compact('stats', 'trends', 'casesPerDept', 'casesPerSeverity', 'studentsWithViolations', 'recentCases', 'monthlyTrend', 'topViolations');
        });

        return Inertia::render('Dashboard', $cachedData);
    }
}

