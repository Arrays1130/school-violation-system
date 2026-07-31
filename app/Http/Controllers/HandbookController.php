<?php

namespace App\Http\Controllers;

use App\Models\Handbook;
use App\Services\HandbookDocumentService;
use App\Support\AttachmentStorage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HandbookController extends Controller
{
    public function __construct(
        protected HandbookDocumentService $documents
    ) {
        $this->authorizeResource(Handbook::class, 'handbook');
    }

    public function index(Request $request)
    {
        $query = Handbook::query();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        $handbooks = $query->latest()->paginate(10);

        if ($request->wantsJson()) {
            return response()->json($handbooks);
        }

        return inertia('Handbooks/Index', [
            'handbooks' => $handbooks,
            'filters' => ['search' => $request->search],
        ]);
    }

    public function create()
    {
        return inertia('Handbooks/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'attachment' => 'nullable|url|max:2048',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        if (blank($validated['content'] ?? null) && ! $request->hasFile('document')) {
            throw ValidationException::withMessages([
                'content' => 'Add policy content or upload a PDF/document.',
            ]);
        }

        $payload = [
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'attachment' => $validated['attachment'] ?? null,
        ];

        if ($request->hasFile('document')) {
            $payload = array_merge($payload, $this->documents->storeUploadedFile($request->file('document')));
        }

        $handbook = Handbook::create($payload);
        $this->backfillContentFromPdf($handbook);

        return redirect()->route('handbooks.index')->with('success', 'Handbook entry created successfully.');
    }

    public function show(Handbook $handbook)
    {
        return inertia('Handbooks/Show', [
            'handbook' => $handbook,
        ]);
    }

    public function edit(Handbook $handbook)
    {
        return inertia('Handbooks/Edit', [
            'handbook' => $handbook,
        ]);
    }

    public function update(Request $request, Handbook $handbook)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'attachment' => 'nullable|url|max:2048',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'remove_document' => 'nullable|boolean',
        ]);

        $removeDocument = $request->boolean('remove_document');
        $willHaveFile = $request->hasFile('document')
            || ($handbook->file_path && ! $removeDocument);

        if (blank($validated['content'] ?? null) && ! $willHaveFile) {
            throw ValidationException::withMessages([
                'content' => 'Add policy content or upload a PDF/document.',
            ]);
        }

        $payload = [
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'attachment' => $validated['attachment'] ?? null,
        ];

        $oldPath = $handbook->file_path;

        if ($request->hasFile('document')) {
            if ($oldPath) {
                $this->documents->deleteStoredFile($oldPath);
            }
            $payload = array_merge($payload, $this->documents->storeUploadedFile($request->file('document')));
        } elseif ($removeDocument && $oldPath) {
            $this->documents->deleteStoredFile($oldPath);
            $payload['file_path'] = null;
            $payload['file_name'] = null;
            $payload['file_size'] = null;
        }

        $handbook->update($payload);
        $this->backfillContentFromPdf($handbook->fresh());

        return redirect()->route('handbooks.index')->with('success', 'Handbook entry updated successfully.');
    }

    public function destroy(Handbook $handbook)
    {
        $this->documents->deleteStoredFile($handbook->file_path);
        $handbook->delete();

        return redirect()->route('handbooks.index')->with('success', 'Handbook entry deleted successfully.');
    }

    public function download(Handbook $handbook)
    {
        $this->authorize('view', $handbook);

        abort_unless($handbook->file_path, 404);

        if (! AttachmentStorage::disk()->exists($handbook->file_path)) {
            abort(404, 'Document file not found.');
        }

        return AttachmentStorage::disk()->download(
            $handbook->file_path,
            $handbook->file_name ?: basename($handbook->file_path)
        );
    }

    /**
     * If content is empty but a PDF was uploaded, copy extracted text into content for search/Nexus.
     */
    protected function backfillContentFromPdf(?Handbook $handbook): void
    {
        if (! $handbook || filled(trim((string) $handbook->content)) || ! $handbook->file_path) {
            return;
        }

        $extracted = $this->documents->extractTextFromStoredPdf($handbook);
        if ($extracted === '') {
            return;
        }

        $handbook->update(['content' => $extracted]);
    }
}
