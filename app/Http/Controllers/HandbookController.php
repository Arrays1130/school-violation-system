<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HandbookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Handbook::query();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        $handbooks = $query->latest()->paginate(10);
        
        if ($request->wantsJson()) {
            return response()->json($handbooks);
        }

        return view('handbooks.index', compact('handbooks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('handbooks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'attachment' => 'nullable|string', // Simple string input for now, consistent with plan
        ]);

        \App\Models\Handbook::create($request->all());

        return redirect()->route('handbooks.index')->with('success', 'Handbook entry created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(\App\Models\Handbook $handbook)
    {
        return view('handbooks.show', compact('handbook'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\Handbook $handbook)
    {
        return view('handbooks.edit', compact('handbook'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, \App\Models\Handbook $handbook)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'attachment' => 'nullable|string',
        ]);

        $handbook->update($request->all());

        return redirect()->route('handbooks.index')->with('success', 'Handbook entry updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Handbook $handbook)
    {
        $handbook->delete();
        return redirect()->route('handbooks.index')->with('success', 'Handbook entry deleted successfully.');
    }
}
