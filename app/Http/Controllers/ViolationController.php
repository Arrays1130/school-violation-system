<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ViolationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Violation::class, 'violation');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Violation::query();

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                  ->orWhere('title', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $violations = $query->latest()->paginate(10);
        $categories = \App\Models\Violation::distinct()->whereNotNull('category')->pluck('category');

        return \Inertia\Inertia::render('Violations/Index', [
            'violations' => $violations,
            'categories' => $categories,
            'filters' => request()->all('search', 'category', 'severity')
        ]);
    }

    public function create()
    {
        return inertia('Violations/Create');
    }

    public function store(\App\Http\Requests\StoreViolationRequest $request)
    {
        \App\Models\Violation::create($request->validated());
        return redirect()->route('violations.index')->with('success', 'Violation created successfully.');
    }

    public function show(Request $request, \App\Models\Violation $violation)
    {
        $cases = \App\Models\StudentCase::query()
            ->where('violation_id', $violation->id)
            ->with(['student:id,full_name,department,section'])
            ->forUser($request->user())
            ->latest('occurred_at')
            ->paginate(15)
            ->withQueryString();

        return inertia('Violations/Show', [
            'violation' => $violation,
            'cases' => $cases,
        ]);
    }

    public function edit(\App\Models\Violation $violation)
    {
        return inertia('Violations/Edit', compact('violation'));
    }

    public function update(\App\Http\Requests\UpdateViolationRequest $request, \App\Models\Violation $violation)
    {
        $violation->update($request->validated());
        return redirect()->route('violations.index')->with('success', 'Violation updated successfully.');
    }

    public function destroy(\App\Models\Violation $violation)
    {
        $violation->delete();
        return redirect()->route('violations.index')->with('success', 'Violation deleted successfully.');
    }

    public function search(Request $request)
    {
        $term = $request->query('term');
        
        $violations = \App\Models\Violation::where('code', 'like', "%{$term}%")
            ->orWhere('title', 'like', "%{$term}%")
            ->take(10)
            ->get(['id', 'code', 'title']);
            
        return response()->json($violations);
    }

    public function getSanctionInfo(Request $request)
    {
        $request->validate([
            'student_id'   => 'nullable|exists:students,id',
            'violation_id' => 'required|exists:violations,id',
        ]);

        $violation = \App\Models\Violation::findOrFail($request->violation_id);
        $advice = app(\App\Services\OffenseAdviceService::class);

        $offenseCount = 0;
        $currentOffenseLevel = 1;
        $suggestedSanction = $advice->sanctionFor($violation, 1);

        if ($request->filled('student_id')) {
            $studentId = (int) $request->student_id;
            $offenseCount = $advice->priorSameViolationCount($studentId, (int) $violation->id);
            $currentOffenseLevel = $advice->offenseLevelFor($studentId, (int) $violation->id);
            $suggestedSanction = $advice->sanctionFor($violation, $currentOffenseLevel);
        }

        return response()->json([
            'sanction'              => $suggestedSanction,
            'severity'              => $violation->severity,
            'offense_count'         => $offenseCount,
            'offense_level'         => $currentOffenseLevel,
            'current_offense_level' => $currentOffenseLevel,
            'suggested_sanction'    => $suggestedSanction,
            'auto_endorse'          => $violation->severity === 'Major',
        ]);
    }
}
