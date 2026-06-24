<?php

namespace App\Http\Controllers;

use App\Models\CaseAttachment;
use App\Models\StudentCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CaseAttachmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        // Fetch Attachments
        $attachmentQuery = CaseAttachment::with(['case.student', 'uploader']);
        if ($search) {
            $attachmentQuery->where(function($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%")
                  ->orWhereHas('case.student', function($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }
        $attachments = $attachmentQuery->get();

        // Fetch Typed Minutes
        $minuteQuery = \App\Models\MeetingMinute::with(['case.student', 'creator']);
        if ($search) {
            $minuteQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhereHas('case.student', function($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }
        $minutes = $minuteQuery->get();

        // Standardize and Merge
        $records = $attachments->map(function($item) {
            return (object)[
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
        })->concat($minutes->map(function($item) {
            return (object)[
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
        }))->sortByDesc('created_at');

        // Paginate the combined collection manually
        $perPage = 10;
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $records = new \Illuminate\Pagination\LengthAwarePaginator(
            $records->forPage($page, $perPage),
            $records->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // Stats
        $totalFiles = CaseAttachment::count() + \App\Models\MeetingMinute::count();
        $pdfFiles = CaseAttachment::where('file_name', 'LIKE', '%.pdf')->count();
        $totalSizeRaw = CaseAttachment::sum('file_size');
        $totalSizeMB = round($totalSizeRaw / (1024 * 1024), 2);

        $cases = StudentCase::with('student', 'violation')->latest()->get();

        return inertia('Minutes/Index', [
            'records' => $records, 
            'totalFiles' => $totalFiles, 
            'pdfFiles' => $pdfFiles, 
            'totalSizeMB' => $totalSizeMB,
            'cases' => $cases
        ]);
    }

    public function create()
    {
        return view('meeting-minutes.create');
    }

    public function store(Request $request)
    {
        // If it's a file upload (from the Document Repository modal)
        if ($request->hasFile('file')) {
            $request->validate([
                'case_id' => 'required|exists:cases,id',
                'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
                'label' => 'nullable|string|max:255',
            ]);

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('attachments', $fileName, 'local');

            CaseAttachment::create([
                'case_id' => $request->case_id,
                'uploaded_by' => Auth::id(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'label' => $request->label,
            ]);

            return redirect()->route('meeting-minutes.index')->with('success', 'Document uploaded successfully.');
        }

        // If it's a typed minute (from the Record Meeting Minutes page)
        $validated = $request->validate([
            'case_id' => 'nullable|exists:cases,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meeting_date' => 'required|date',
            'venue' => 'required|string|max:255',
        ]);

        $validated['created_by'] = Auth::id();

        \App\Models\MeetingMinute::create($validated);

        return redirect()->route('meeting-minutes.index')->with('success', 'Meeting minutes recorded successfully.');
    }

    public function view(CaseAttachment $attachment)
    {
        return view('attachments.view', compact('attachment'));
    }

    public function download(CaseAttachment $attachment)
    {
        if (!Storage::disk('local')->exists($attachment->file_path)) {
            return back()->with('error', 'File not found on server.');
        }

        return Storage::disk('local')->download($attachment->file_path, $attachment->file_name);
    }

    public function destroy(CaseAttachment $attachment)
    {
        Storage::disk('local')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Document deleted successfully.');
    }
}
