<?php

namespace App\Http\Controllers;

use App\Models\Hearing;
use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use App\Services\AuditLogFormatter;
use App\Support\DepartmentResolver;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function __construct(
        protected AuditLogFormatter $formatter
    ) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->isDean(), 403, 'Unauthorized access to audit logs.');

        $query = $this->buildQuery($request);

        $stats = [
            'total' => (clone $query)->count(),
            'today' => (clone $query)->whereDate('created_at', today())->count(),
            'created' => (clone $query)->where('event', 'created')->count(),
            'updated' => (clone $query)->where('event', 'updated')->count(),
            'deleted' => (clone $query)->where('event', 'deleted')->count(),
        ];

        $logs = $query->paginate(20)->appends($request->all());

        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role']);
        $subjectTypes = $this->subjectTypeOptions();

        return view('reports.audit-logs', compact('logs', 'stats', 'users', 'subjectTypes'));
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->isDean(), 403, 'Unauthorized access to audit logs.');

        $query = $this->buildQuery($request);

        $filename = 'audit_logs_'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Timestamp',
                'User',
                'Email',
                'Action',
                'Module',
                'Record ID',
                'Changed Fields',
                'Description',
            ]);

            $query->chunk(200, function ($logs) use ($file) {
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->causer->name ?? 'System',
                        $log->causer->email ?? '',
                        ucfirst($log->event ?? 'unknown'),
                        $this->formatter->subjectLabel($log),
                        $log->subject_id ?? '',
                        $this->formatter->changeSummary($log),
                        $log->description,
                    ]);
                }
            });

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function buildQuery(Request $request)
    {
        $user = $request->user();

        $query = Activity::with(['causer', 'subject'])->latest();

        if ($user->isDean()) {
            $this->scopeForDean($query, $user);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('subject_id', 'like', "%{$search}%")
                    ->orWhereHas('causer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    protected function scopeForDean($query, User $user): void
    {
        $department = DepartmentResolver::shortcutToLong($user->department);

        $caseIds = StudentCase::whereHas('student', fn ($q) => $q->where('department', $department))->pluck('id');
        $studentIds = Student::where('department', $department)->pluck('id');
        $hearingIds = Hearing::whereIn('case_id', $caseIds)->pluck('id');

        $query->where(function ($q) use ($caseIds, $studentIds, $hearingIds) {
            $q->where(function ($q2) use ($caseIds) {
                $q2->where('subject_type', StudentCase::class)
                    ->whereIn('subject_id', $caseIds);
            })->orWhere(function ($q2) use ($studentIds) {
                $q2->where('subject_type', Student::class)
                    ->whereIn('subject_id', $studentIds);
            })->orWhere(function ($q2) use ($hearingIds) {
                $q2->where('subject_type', Hearing::class)
                    ->whereIn('subject_id', $hearingIds);
            })->orWhere(function ($q2) {
                $q2->where('subject_type', Violation::class);
            });
        });
    }

    /**
     * @return array<string, string>
     */
    protected function subjectTypeOptions(): array
    {
        return [
            StudentCase::class => 'Violation Case',
            Student::class => 'Student',
            User::class => 'User Account',
            Hearing::class => 'Hearing',
            Violation::class => 'Violation Rule',
        ];
    }
}
