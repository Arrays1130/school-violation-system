<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentCase;
use Illuminate\Http\Request;

class ViolationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = StudentCase::with(['student', 'violation']);

        // Filter based on role
        if ($user->isDean()) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('department', $user->department);
            });
        }

        $violations = $query->latest()->paginate(15);

        return response()->json($violations);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $case = StudentCase::with(['student', 'violation', 'creator', 'attachments', 'hearings'])->findOrFail($id);

        // Access control for Dean
        if ($user->isDean() && $case->student->department !== $user->department) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($case);
    }

    public function stats(Request $request)
    {
        $user = $request->user();
        $dept = $user->department;

        // 1. Status Counts
        $counts = StudentCase::whereHas('student', function($q) use ($dept) {
                $q->where('department', $dept);
            })
            ->selectRaw("status, count(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status');

        $total = $counts->sum();
        $pending = $counts->get('Pending', 0) + $counts->get('Hearing Scheduled', 0) + $counts->get('Hearing', 0);
        $resolved = $counts->get('Closed', 0);

        // 2. Top Offenses
        $topOffenses = StudentCase::whereHas('student', function($q) use ($dept) {
                $q->where('department', $dept);
            })
            ->join('violations', 'cases.violation_id', '=', 'violations.id')
            ->selectRaw('violations.title, count(*) as count')
            ->groupBy('violations.title')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 3. Upcoming Hearings
        $upcomingHearings = \App\Models\Hearing::whereHas('case.student', function($q) use ($dept) {
                $q->where('department', $dept);
            })
            ->with(['case.student', 'case.violation'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->get();

        // 4. Severity Distribution (for Pie Chart)
        $severityStats = StudentCase::whereHas('student', function($q) use ($dept) {
                $q->where('department', $dept);
            })
            ->join('violations', 'cases.violation_id', '=', 'violations.id')
            ->selectRaw('violations.severity, count(*) as count')
            ->groupBy('violations.severity')
            ->pluck('count', 'severity');

        // 5. Monthly Trends (Last 6 Months for Bar Chart)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = StudentCase::whereHas('student', function($q) use ($dept) {
                    $q->where('department', $dept);
                })
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            
            $monthlyTrends[] = [
                'month' => $month->format('M'),
                'count' => $count,
            ];
        }

        return response()->json([
            'summary' => [
                'total' => $total,
                'pending' => $pending,
                'resolved' => $resolved,
            ],
            'top_offenses' => $topOffenses,
            'severity_stats' => $severityStats,
            'monthly_trends' => $monthlyTrends,
            'upcoming_hearings' => $upcomingHearings,
        ]);
    }
}
