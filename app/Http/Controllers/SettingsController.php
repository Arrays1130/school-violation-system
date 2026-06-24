<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        // Require Super Admin or Dean, or maybe just anyone who has access to Settings. Let's restrict via route middleware or here.
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isDean()) {
            abort(403, 'Unauthorized access to system settings.');
        }

        $currentAcademicYear = \App\Models\SystemSetting::where('key', 'current_academic_year')->value('value');
        return view('settings.index', compact('currentAcademicYear'));
    }

    public function update(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isDean()) {
            abort(403, 'Unauthorized access to system settings.');
        }

        $validated = $request->validate([
            'current_academic_year' => 'required|string|max:255'
        ]);

        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'current_academic_year'],
            ['value' => $validated['current_academic_year']]
        );

        return redirect()->back()->with('success', 'System settings updated successfully.');
    }

    public function archiveClosedCases(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isDean()) {
            abort(403, 'Unauthorized access to system settings.');
        }

        // Archive only closed cases that are not already archived
        $count = \App\Models\StudentCase::where('status', 'Closed')
            ->where('is_archived', false)
            ->update(['is_archived' => true]);

        // Clear dashboard cache to reflect the changes
        \App\Models\StudentCase::clearDashboardCache();

        return redirect()->back()->with('success', "Successfully archived {$count} closed cases. They are now hidden from the main dashboard but can still be found in Record Retrieval.");
    }
}
