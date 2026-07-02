<?php

namespace App\Http\Controllers;

use App\Models\CaseAttachment;
use App\Models\MeetingMinute;
use App\Models\StudentCase;
use App\Support\DepartmentResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\AttachmentStorage;

class CaseAttachmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $user = $request->user();
        $perPage = 10;
        $page = (int) ($request->input('page', 1));

        $fileQuery = DB::table('case_attachments as ca')
            ->join('cases as c', 'ca.case_id', '=', 'c.id')
            ->join('students as s', 'c.student_id', '=', 's.id')
            ->whereNull('ca.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereNull('s.deleted_at')
            ->select([
                'ca.id',
                DB::raw("'file' as record_type"),
                DB::raw('COALESCE(ca.label, ca.file_name) as label'),
                'ca.created_at',
                'ca.case_id',
            ]);

        $minuteQuery = DB::table('meeting_minutes as mm')
            ->leftJoin('cases as c', 'mm.case_id', '=', 'c.id')
            ->leftJoin('students as s', 'c.student_id', '=', 's.id')
            ->whereNull('mm.deleted_at')
            ->where(function ($q) {
                $q->whereNull('mm.case_id')
                    ->orWhere(function ($inner) {
                        $inner->whereNull('c.deleted_at')->whereNull('s.deleted_at');
                    });
            })
            ->select([
                'mm.id',
                DB::raw("'text' as record_type"),
                'mm.title as label',
                'mm.created_at',
                'mm.case_id',
            ]);

        if ($user->isDean()) {
            $department = trim((string) DepartmentResolver::shortcutToLong($user->department));
            $fileQuery->whereRaw('TRIM(s.department) = ?', [$department]);
            $minuteQuery->where(function ($q) use ($department) {
                $q->whereNull('mm.case_id')
                    ->orWhereRaw('TRIM(s.department) = ?', [$department]);
            });
        }

        if ($search) {
            $like = '%'.$search.'%';
            $fileQuery->where(function ($q) use ($like) {
                $q->where('ca.label', 'like', $like)
                    ->orWhere('ca.file_name', 'like', $like)
                    ->orWhere('s.full_name', 'like', $like);
            });
            $minuteQuery->where(function ($q) use ($like) {
                $q->where('mm.title', 'like', $like)
                    ->orWhere('mm.content', 'like', $like)
                    ->orWhere('s.full_name', 'like', $like);
            });
        }

        $union = $fileQuery->unionAll($minuteQuery);
        $total = DB::query()->fromSub($union, 'records')->count();

        $pageRows = DB::query()
            ->fromSub($union, 'records')
            ->orderByDesc('created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $fileIds = $pageRows->where('record_type', 'file')->pluck('id');
        $minuteIds = $pageRows->where('record_type', 'text')->pluck('id');

        $attachments = CaseAttachment::with(['case.student', 'uploader'])
            ->whereIn('id', $fileIds)
            ->get()
            ->keyBy('id');

        $minutes = MeetingMinute::with(['case.student', 'creator'])
            ->whereIn('id', $minuteIds)
            ->get()
            ->keyBy('id');

        $items = $pageRows->map(function ($row) use ($attachments, $minutes) {
            if ($row->record_type === 'file') {
                $item = $attachments->get($row->id);
                if (! $item) {
                    return null;
                }

                return (object) [
                    'id' => $item->id,
                    'type' => 'file',
                    'icon' => $item->file_icon,
                    'label' => $item->label ?? $item->file_name,
                    'created_at' => $item->created_at,
                    'case' => $item->case,
                    'uploader' => $item->uploader->name ?? 'System',
                    'size' => $item->formatted_size,
                    'view_url' => route('attachments.view', $item),
                    'download_url' => route('attachments.download', $item),
                    'delete_url' => route('attachments.destroy', $item),
                ];
            }

            $item = $minutes->get($row->id);
            if (! $item) {
                return null;
            }

            return (object) [
                'id' => $item->id,
                'type' => 'text',
                'icon' => 'file-edit',
                'label' => $item->title,
                'created_at' => $item->created_at,
                'case' => $item->case,
                'uploader' => $item->creator->name ?? 'System',
                'size' => '-',
                'view_url' => route('meeting-minutes.show', $item),
                'download_url' => null,
                'delete_url' => route('meeting-minutes.destroy', $item),
            ];
        })->filter()->values();

        $records = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $statsQuery = CaseAttachment::query();
        if ($user->isDean()) {
            $statsQuery->whereHas('case', fn ($q) => $q->forUser($user));
        }
        $minuteStatsQuery = MeetingMinute::query();
        if ($user->isDean()) {
            $minuteStatsQuery->where(function ($q) use ($user) {
                $q->whereNull('case_id')
                    ->orWhereHas('case', fn ($cq) => $cq->forUser($user));
            });
        }

        $totalFiles = $statsQuery->count() + $minuteStatsQuery->count();
        $pdfFiles = (clone $statsQuery)->where('file_name', 'LIKE', '%.pdf')->count();
        $totalSizeRaw = (clone $statsQuery)->sum('file_size');
        $totalSizeMB = round($totalSizeRaw / (1024 * 1024), 2);

        $casesQuery = StudentCase::with('student', 'violation')
            ->forUser($user)
            ->latest()
            ->limit(50);

        return inertia('Minutes/Index', [
            'records' => $records,
            'totalFiles' => $totalFiles,
            'pdfFiles' => $pdfFiles,
            'totalSizeMB' => $totalSizeMB,
            'cases' => $casesQuery->get(),
        ]);
    }

    public function store(\App\Http\Requests\StoreCaseAttachmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $case = StudentCase::findOrFail($validated['case_id']);
        $this->saveAttachment($request, $case);

        return redirect()->route('meeting-minutes.index')->with('success', 'Document uploaded successfully.');
    }

    public function storeForCase(\App\Http\Requests\StoreCaseAttachmentRequest $request, StudentCase $case): RedirectResponse
    {
        $this->saveAttachment($request, $case);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function view(CaseAttachment $attachment)
    {
        $this->resolveAuthorizedCase($attachment);

        $ext = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));

        return inertia('Attachments/View', [
            'attachment' => [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'label' => $attachment->label,
                'file_ext' => $ext,
                'download_url' => route('attachments.download', $attachment),
            ],
        ]);
    }

    public function download(CaseAttachment $attachment)
    {
        $this->resolveAuthorizedCase($attachment);

        if (! AttachmentStorage::disk()->exists($attachment->file_path)) {
            return back()->with('error', 'File not found on server.');
        }

        return AttachmentStorage::disk()->download($attachment->file_path, $attachment->file_name);
    }

    public function destroy(CaseAttachment $attachment)
    {
        $case = $this->resolveAuthorizedCase($attachment);
        $this->authorize('update', $case);

        AttachmentStorage::disk()->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    protected function saveAttachment(Request $request, StudentCase $case): void
    {
        $this->authorize('update', $case);

        $file = $request->file('file');
        $fileName = time().'_'.$file->getClientOriginalName();
        $filePath = $file->storeAs('attachments', $fileName, (string) config('filesystems.attachments_disk', 'local'));

        CaseAttachment::create([
            'case_id' => $case->id,
            'uploaded_by' => Auth::id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'label' => $request->label,
        ]);
    }

    protected function resolveAuthorizedCase(CaseAttachment $attachment): StudentCase
    {
        $attachment->loadMissing('case');
        $case = $attachment->case;
        abort_if(! $case, 404);
        $this->authorize('view', $case);

        return $case;
    }
}
