<?php

namespace App\Http\Controllers;

use App\Support\DashboardCache;
use App\Support\SchoolSettings;
use App\Support\StudentPromotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $previousYear = (string) SchoolSettings::get('current_academic_year', '');
        $newYear = $validated['current_academic_year'];
        $yearChanged = $previousYear !== $newYear;

        SchoolSettings::set('current_academic_year', $newYear);
        SchoolSettings::set('school_name', $validated['school_name']);

        if (! $yearChanged) {
            return redirect()->back()->with('success', 'System settings updated successfully.');
        }

        $promoted = StudentPromotion::promoteYearLevels();
        StudentPromotion::rollForwardAcademicYear($newYear);

        DashboardCache::bust();
        try {
            event(new \App\Events\DashboardUpdated('Academic year changed; students promoted'));
        } catch (\Exception $e) {
            Log::warning('Dashboard event dispatch failed after academic year change', ['error' => $e->getMessage()]);
        }

        $message = "System settings updated. Academic year is now {$newYear}.";
        if ($promoted > 0) {
            $message .= " Promoted {$promoted} student".($promoted === 1 ? '' : 's').' to the next year level.';
        } else {
            $message .= ' No 1st–3rd year students were available to promote.';
        }
        $message .= ' All active students were updated to the new academic year.';

        return redirect()->back()->with('success', $message);
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
