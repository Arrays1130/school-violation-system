<?php

namespace App\Http\Controllers;

use App\Support\SchoolSettings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        if (! auth()->user()->isSuperAdmin() && ! auth()->user()->isDean()) {
            abort(403, 'Unauthorized access to system settings.');
        }

        $currentYear = (int) date('Y');
        $academicYears = collect(range($currentYear - 5, $currentYear + 5))
            ->map(fn ($y) => "SY {$y}-".($y + 1))
            ->values();

        $closedCasesToArchive = \App\Models\StudentCase::where('status', 'Closed')
            ->where('is_archived', false)
            ->count();

        return inertia('Settings/Index', [
            'currentAcademicYear' => SchoolSettings::get('current_academic_year'),
            'schoolName' => SchoolSettings::get('school_name', config('school.name')),
            'closedCasesToArchive' => $closedCasesToArchive,
            'canArchive' => auth()->user()->isSuperAdmin(),
            'canUpdate' => auth()->user()->isSuperAdmin(),
            'academicYears' => $academicYears,
        ]);
    }

    public function update(Request $request)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super administrators can update system settings.');
        }

        $validated = $request->validate([
            'current_academic_year' => 'required|string|max:255',
            'school_name' => 'required|string|max:255',
        ]);

        SchoolSettings::set('current_academic_year', $validated['current_academic_year']);
        SchoolSettings::set('school_name', $validated['school_name']);

        return redirect()->back()->with('success', 'System settings updated successfully.');
    }

    public function archiveClosedCases(Request $request)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super administrators can archive closed cases.');
        }

        $count = \App\Models\StudentCase::where('status', 'Closed')
            ->where('is_archived', false)
            ->update(['is_archived' => true]);

        \App\Models\StudentCase::clearDashboardCache();

        return redirect()->back()->with('success', "Successfully archived {$count} closed cases. They are now hidden from the main dashboard but can still be found in Record Retrieval.");
    }
}
