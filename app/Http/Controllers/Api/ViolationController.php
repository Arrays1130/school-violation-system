<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ViolationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = StudentCase::with(['student', 'violation']);

        // Filter based on role
        if ($user->isDean()) {
            $query->whereHas('student', function ($q) use ($user) {
                $longName = \App\Models\Student::resolveDepartmentLongName($user->department);
                $q->where(function($sub) use ($user, $longName) {
                    $sub->where('department', $user->department)
                        ->orWhere('department', $longName);
                });
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
        if ($user->isDean()) {
            $longName = \App\Models\Student::resolveDepartmentLongName($user->department);
            if ($case->student->department !== $longName && $case->student->department !== $user->department) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return response()->json($case);
    }

    public function stats(Request $request)
    {
        $user = $request->user();
        $dept = $user->department;
        
        $cacheKey = 'mobile_dashboard_stats_' . md5($dept);
        
        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($dept) {
            $longDept = \App\Models\Student::resolveDepartmentLongName($dept);

            // 1. Status Counts
            $counts = StudentCase::whereHas('student', function($q) use ($dept, $longDept) {
                    $q->where(function($sub) use ($dept, $longDept) {
                        $sub->where('department', $dept)->orWhere('department', $longDept);
                    });
                })
                ->selectRaw("status, count(*) as count")
                ->groupBy('status')
                ->pluck('count', 'status');

        $total = $counts->sum();
        $pending = $counts->get('Pending', 0) + $counts->get('Hearing Scheduled', 0) + $counts->get('Hearing', 0);
        $resolved = $counts->get('Closed', 0);

        // 2. Top Offenses
        $topOffenses = StudentCase::whereHas('student', function($q) use ($dept, $longDept) {
                $q->where(function($sub) use ($dept, $longDept) {
                    $sub->where('department', $dept)->orWhere('department', $longDept);
                });
            })
            ->join('violations', 'cases.violation_id', '=', 'violations.id')
            ->selectRaw('violations.title, count(*) as count')
            ->groupBy('violations.title')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 3. Upcoming Hearings
        $upcomingHearings = \App\Models\Hearing::whereHas('case.student', function($q) use ($dept, $longDept) {
                $q->where(function($sub) use ($dept, $longDept) {
                    $sub->where('department', $dept)->orWhere('department', $longDept);
                });
            })
            ->with(['case.student', 'case.violation'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->get();

        // 4. Severity Distribution (for Pie Chart)
        $severityStats = StudentCase::whereHas('student', function($q) use ($dept, $longDept) {
                $q->where(function($sub) use ($dept, $longDept) {
                    $sub->where('department', $dept)->orWhere('department', $longDept);
                });
            })
            ->join('violations', 'cases.violation_id', '=', 'violations.id')
            ->selectRaw('violations.severity, count(*) as count')
            ->groupBy('violations.severity')
            ->pluck('count', 'severity');

            // 5. Monthly Trends (Last 6 Months for Bar Chart)
            $rawMonthlyTrend = StudentCase::whereHas('student', function($q) use ($dept, $longDept) {
                    $q->where(function($sub) use ($dept, $longDept) {
                        $sub->where('department', $dept)->orWhere('department', $longDept);
                    });
                })
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
                ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
                ->groupBy('month')
                ->pluck('count', 'month');

            $monthlyTrends = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->startOfMonth()->subMonths($i);
                $monthlyTrends[] = [
                    'month' => $month->format('M'),
                    'count' => $rawMonthlyTrend->get($month->format('Y-m'), 0),
                ];
            }

            return [
                'summary' => [
                    'total' => $total,
                    'pending' => $pending,
                    'resolved' => $resolved,
                ],
                'top_offenses' => $topOffenses,
                'severity_stats' => $severityStats->isEmpty() ? (object)[] : $severityStats,
                'monthly_trends' => $monthlyTrends,
                'upcoming_hearings' => $upcomingHearings,
            ];
        });

        return response()->json($data);
    }
}
