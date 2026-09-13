<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanctionAssignment;
use App\Services\SanctionAssignmentService;
use Illuminate\Http\Request;

class GsoSanctionController extends Controller
{
    public function __construct(private SanctionAssignmentService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeGso($request);

        $status = $request->input('status', 'in_progress');
        $query = SanctionAssignment::query()
            ->with(['studentCase.student', 'studentCase.violation', 'dtrEntries'])
            ->latest();

        if ($status === 'active') {
            $query->where('status', SanctionAssignment::STATUS_IN_PROGRESS);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        $items = $query->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'data' => collect($items->items())->map(fn (SanctionAssignment $a) => $a->toApiArray())->values(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function show(Request $request, SanctionAssignment $assignment)
    {
        $this->authorizeGso($request);

        $assignment->load(['studentCase.student', 'studentCase.violation', 'dtrEntries.loggedBy']);

        return response()->json($assignment->toApiArray());
    }

    public function timeIn(Request $request, SanctionAssignment $assignment)
    {
        $this->authorizeGso($request);

        $data = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $entry = $this->service->timeIn($assignment, $request->user(), $data['notes'] ?? null);

        return response()->json([
            'message' => 'Time in recorded.',
            'entry' => $entry->toApiArray(),
            'assignment' => $assignment->fresh(['dtrEntries', 'studentCase.student', 'studentCase.violation'])->toApiArray(),
        ]);
    }

    public function timeOut(Request $request, SanctionAssignment $assignment)
    {
        $this->authorizeGso($request);

        $data = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $entry = $this->service->timeOut($assignment, $request->user(), $data['notes'] ?? null);

        return response()->json([
            'message' => 'Time out recorded.',
            'entry' => $entry->toApiArray(),
            'assignment' => $assignment->fresh(['dtrEntries', 'studentCase.student', 'studentCase.violation'])->toApiArray(),
        ]);
    }

    public function complete(Request $request, SanctionAssignment $assignment)
    {
        $this->authorizeGso($request);

        $data = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $updated = $this->service->complete($assignment, $request->user(), $data['notes'] ?? null);

        return response()->json([
            'message' => 'Sanction marked complete and forwarded to OSA.',
            'assignment' => $updated->toApiArray(),
        ]);
    }

    protected function authorizeGso(Request $request): void
    {
        $user = $request->user();
        if (! $user || (! $user->isGso() && ! $user->isSuperAdmin())) {
            abort(403, 'GSO access required.');
        }
    }
}
