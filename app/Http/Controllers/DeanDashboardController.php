<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\StudentCase;
use App\Models\Student;
use App\Models\Violation;
use Inertia\Inertia;

class DeanDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user->isDean() && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $department = !$user->isSuperAdmin() ? $user->department : 'All Departments';
        
        // Cache heavy metrics, lists, and charts per department to deliver exceptional capstone performance
        $cacheKey = 'dean_dashboard.data.' . md5($department);
        $cachedData = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($department, $user) {
            // Base Query for students in the department
            $studentQuery = \App\Models\Student::query();
            if (!$user->isSuperAdmin()) {
                $longName = \App\Models\Student::resolveDepartmentLongName($department);
                $studentQuery->where('department', $longName);
            }
            $studentIds = $studentQuery->pluck('id');

            // --- Stats & Trends ---
            $stats = [
                'total' => StudentCase::active()->whereIn('student_id', $studentIds)->count(),
                'pending' => StudentCase::active()->whereIn('student_id', $studentIds)->where('status', 'Pending')->count(),
                'in_progress' => StudentCase::active()->whereIn('student_id', $studentIds)->whereIn('status', ['Hearing Scheduled', 'Hearing'])->count(),
                'closed' => StudentCase::active()->whereIn('student_id', $studentIds)->where('status', 'Closed')->count(),
            ];

            // Previous month stats for trend
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $prevStats = [
                'total' => StudentCase::active()->whereIn('student_id', $studentIds)->where('created_at', '<=', $lastMonthEnd)->count(),
                'pending' => StudentCase::active()->whereIn('student_id', $studentIds)->where('status', 'Pending')->where('created_at', '<=', $lastMonthEnd)->count(),
                'closed' => StudentCase::active()->whereIn('student_id', $studentIds)->where('status', 'Closed')->where('created_at', '<=', $lastMonthEnd)->count(),
            ];

            $trends = [];
            foreach (['total', 'pending', 'closed'] as $key) {
                $current = $stats[$key];
                $prev = $prevStats[$key];
                $diff = $current - $prev;
                $percentage = $prev > 0 ? round(($diff / $prev) * 100, 1) : ($current > 0 ? 100 : 0);
                $trends[$key] = [
                    'percentage' => abs($percentage),
                    'direction' => $diff >= 0 ? 'up' : 'down',
                    'isPositive' => $key === 'closed' ? $diff >= 0 : $diff <= 0
                ];
            }

            // --- Chart Data ---
            // 1. Monthly Trend (Last 6 Months)
            $rawMonthlyTrend = StudentCase::active()->whereIn('student_id', $studentIds)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
                ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
                ->groupBy('month')
                ->pluck('count', 'month');

            $monthlyTrend = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->startOfMonth()->subMonths($i)->format('Y-m');
                $monthlyTrend[$month] = $rawMonthlyTrend->get($month, 0);
            }

            // 2. Most Common Violations
            $topViolations = StudentCase::active()->whereIn('student_id', $studentIds)
                ->join('violations', 'cases.violation_id', '=', 'violations.id')
                ->selectRaw('violations.title, COUNT(*) as count')
                ->groupBy('violations.title')
                ->orderByDesc('count')
                ->take(5)
                ->get();

            // 3. Severity Breakdown
            $severityBreakdown = StudentCase::active()->whereIn('student_id', $studentIds)
                ->join('violations', 'cases.violation_id', '=', 'violations.id')
                ->selectRaw('violations.severity, COUNT(*) as count')
                ->groupBy('violations.severity')
                ->pluck('count', 'severity');

            // --- Recent Activity & Notifications ---
            $recentCases = StudentCase::active()->with(['student', 'violation', 'attachments', 'creator'])
                ->whereIn('student_id', $studentIds)
                ->orderBy('created_at', 'desc')
                ->take(8)
                ->get();

            $upcomingHearings = \App\Models\Hearing::whereHas('case', function ($query) use ($studentIds) {
                    $query->whereIn('student_id', $studentIds);
                })
                ->where('scheduled_at', '>=', now()->startOfDay())
                ->with(['case.student', 'case.violation'])
                ->orderBy('scheduled_at', 'asc')
                ->take(5)
                ->get();

            $topRepeaters = \App\Models\Student::whereIn('id', $studentIds)
                ->withCount(['cases' => function ($query) {
                    $query->active();
                }])
                ->having('cases_count', '>', 0)
                ->orderByDesc('cases_count')
                ->take(5)
                ->get();

            return compact('stats', 'trends', 'monthlyTrend', 'topViolations', 'severityBreakdown', 'recentCases', 'upcomingHearings', 'topRepeaters');
        });

        // Real Notifications remain fully dynamic and personalized
        $notifications = $user->unreadNotifications()->take(10)->get();

        return Inertia::render('Dean/Dashboard', [
            'department' => $department,
            'stats' => $cachedData['stats'],
            'trends' => $cachedData['trends'],
            'chartData' => [
                'monthlyTrend' => $cachedData['monthlyTrend'],
                'topViolations' => $cachedData['topViolations'],
                'severityBreakdown' => $cachedData['severityBreakdown'],
            ],
            'recentCases' => $cachedData['recentCases'],
            'upcomingHearings' => $cachedData['upcomingHearings'],
            'notifications' => $notifications,
            'unreadCount' => $user->unreadNotifications()->count(),
            'topRepeaters' => $cachedData['topRepeaters']
        ]);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back();
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    }

}
