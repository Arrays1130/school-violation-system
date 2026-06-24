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
        return view('violations.create');
    }

    public function store(\App\Http\Requests\StoreViolationRequest $request)
    {
        \App\Models\Violation::create($request->validated());
        return redirect()->route('violations.index')->with('success', 'Violation created successfully.');
    }

    public function show(\App\Models\Violation $violation)
    {
        return view('violations.show', compact('violation'));
    }

    public function edit(\App\Models\Violation $violation)
    {
        return view('violations.edit', compact('violation'));
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

        $violationId = $request->violation_id;
        $violation   = \App\Models\Violation::findOrFail($violationId);

        // Default to first-offense when no student context (e.g. before student is selected)
        $suggestedSanction    = $violation->first_offense;
        $currentOffenseLevel  = 1;
        $offenseCount         = 0;

        if ($request->filled('student_id')) {
            $studentId    = $request->student_id;
            $offenseCount = \App\Models\StudentCase::where('student_id', $studentId)
                ->where('violation_id', $violationId)
                ->where('status', '!=', 'Dismissed')
                ->count();

            $currentOffenseLevel = $offenseCount + 1;

            $suggestedSanction = match(true) {
                $currentOffenseLevel === 1 => $violation->first_offense,
                $currentOffenseLevel === 2 => $violation->second_offense,
                default                    => $violation->third_offense,
            };
        }

        return response()->json([
            // Keys expected by the create form Alpine.js
            'sanction'              => $suggestedSanction ?? 'Manual Review Required',
            'severity'              => $violation->severity,
            // Extra context keys
            'offense_count'         => $offenseCount,
            'current_offense_level' => $currentOffenseLevel,
            'suggested_sanction'    => $suggestedSanction ?? 'Manual Review Required',
        ]);
    }
}
