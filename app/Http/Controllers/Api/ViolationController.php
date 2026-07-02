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

        $perPage = min((int) $request->input('per_page', 15), 50);
        $violations = $query->latest()->paginate($perPage);

        return response()->json($violations);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $case = StudentCase::with(['student', 'violation', 'creator', 'attachments', 'hearings', 'actions.user'])->findOrFail($id);

        // Access control for Dean
        if ($user->isDean()) {
            $longName = \App\Models\Student::resolveDepartmentLongName($user->department);
            if ($case->student->department !== $longName && $case->student->department !== $user->department) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $payload = $case->toArray();
        $payload['attachments'] = $case->attachments->map(function ($attachment) {
            $data = $attachment->toArray();
            $data['mobile_download_url'] = url('/api/mobile/attachments/'.$attachment->id.'/download');

            return $data;
        })->values()->all();

        return response()->json($payload);
    }

    public function stats(Request $request)
    {
        $user = $request->user();
        $scope = $user->isDean() && $user->department
            ? 'dean:' . $user->department
            : 'all';

        $cacheKey = 'mobile_dashboard_stats_' . md5($scope);

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user) {
            $applyDeptScope = function ($query) use ($user) {
                if (! $user->isDean() || ! $user->department) {
                    return $query;
                }

                $dept = $user->department;
                $longDept = \App\Models\Student::resolveDepartmentLongName($dept);

                return $query->whereHas('student', function ($q) use ($dept, $longDept) {
                    $q->where(function ($sub) use ($dept, $longDept) {
                        $sub->where('department', $dept)->orWhere('department', $longDept);
                    });
                });
            };

            // 1. Status Counts
            $counts = $applyDeptScope(StudentCase::query())
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

        $total = $counts->sum();
        $pending = $counts->get('Pending', 0) + $counts->get('Hearing Scheduled', 0) + $counts->get('Hearing', 0);
        $resolved = $counts->get('Closed', 0);

        // 2. Top Offenses
        $topOffenses = $applyDeptScope(StudentCase::query())
            ->join('violations', 'cases.violation_id', '=', 'violations.id')
            ->selectRaw('violations.title, count(*) as count')
            ->groupBy('violations.title')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 3. Upcoming Hearings
        $hearingQuery = \App\Models\Hearing::query()
            ->with(['case.student', 'case.violation'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at', 'asc')
            ->limit(5);

        if ($user->isDean() && $user->department) {
            $dept = $user->department;
            $longDept = \App\Models\Student::resolveDepartmentLongName($dept);
            $hearingQuery->whereHas('case.student', function ($q) use ($dept, $longDept) {
                $q->where(function ($sub) use ($dept, $longDept) {
                    $sub->where('department', $dept)->orWhere('department', $longDept);
                });
            });
        }

        $upcomingHearings = $hearingQuery->get();

        // 4. Severity Distribution (for Pie Chart)
        $severityStats = $applyDeptScope(StudentCase::query())
            ->join('violations', 'cases.violation_id', '=', 'violations.id')
            ->selectRaw('violations.severity, count(*) as count')
            ->groupBy('violations.severity')
            ->pluck('count', 'severity');

            // 5. Monthly Trends (Last 6 Months for Bar Chart)
            $rawMonthlyTrend = $applyDeptScope(StudentCase::query())
                ->selectRaw(\App\Support\SqlDate::yearMonth('created_at').' as month, COUNT(*) as count')
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

    public function analytics(Request $request)
    {
        $user = $request->user();
        $scope = $user->isDean() && $user->department
            ? 'dean_analytics:' . $user->department
            : 'all_analytics';

        $cacheKey = 'mobile_analytics_' . md5($scope);

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user) {
            $applyDeptScope = function ($query) use ($user) {
                if (! $user->isDean() || ! $user->department) {
                    return $query;
                }

                $dept = $user->department;
                $longDept = \App\Models\Student::resolveDepartmentLongName($dept);

                return $query->whereHas('student', function ($q) use ($dept, $longDept) {
                    $q->where(function ($sub) use ($dept, $longDept) {
                        $sub->where('department', $dept)->orWhere('department', $longDept);
                    });
                });
            };

            $counts = $applyDeptScope(StudentCase::query())
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $total = $counts->sum();
            $pending = $counts->get('Pending', 0) + $counts->get('Hearing Scheduled', 0) + $counts->get('Hearing', 0);
            $resolved = $counts->get('Closed', 0);

            $severityStats = $applyDeptScope(StudentCase::query())
                ->join('violations', 'cases.violation_id', '=', 'violations.id')
                ->selectRaw('violations.severity, count(*) as count')
                ->groupBy('violations.severity')
                ->pluck('count', 'severity');

            $major = (int) ($severityStats->get('Major', 0) + $severityStats->get('major', 0));
            $minor = (int) ($severityStats->get('Minor', 0) + $severityStats->get('minor', 0));

            $topViolations = $applyDeptScope(StudentCase::query())
                ->join('violations', 'cases.violation_id', '=', 'violations.id')
                ->selectRaw('violations.title as title, count(*) as count')
                ->groupBy('violations.title')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
                ->map(fn ($row) => ['title' => $row->title, 'count' => (int) $row->count])
                ->values();

            $repeatOffenders = $applyDeptScope(StudentCase::query())
                ->join('students', 'cases.student_id', '=', 'students.id')
                ->selectRaw('students.full_name as name, count(*) as count')
                ->groupBy('students.id', 'students.full_name')
                ->having('count', '>', 1)
                ->orderByDesc('count')
                ->limit(5)
                ->get()
                ->map(fn ($row) => ['name' => $row->name, 'count' => (int) $row->count])
                ->values();

            $rawMonthlyTrend = $applyDeptScope(StudentCase::query())
                ->selectRaw(\App\Support\SqlDate::yearMonth('created_at').' as month, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
                ->groupBy('month')
                ->pluck('count', 'month');

            $monthlyTrends = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->startOfMonth()->subMonths($i);
                $monthlyTrends[] = [
                    'month' => $month->format('M'),
                    'count' => (int) $rawMonthlyTrend->get($month->format('Y-m'), 0),
                ];
            }

            return [
                'summary' => [
                    'total' => $total,
                    'pending' => $pending,
                    'resolved' => $resolved,
                    'major' => $major,
                    'minor' => $minor,
                ],
                'top_violations' => $topViolations,
                'repeat_offenders' => $repeatOffenders,
                'severity_stats' => $severityStats->isEmpty() ? (object) [] : $severityStats,
                'monthly_trends' => $monthlyTrends,
            ];
        });

        return response()->json($data);
    }
}
