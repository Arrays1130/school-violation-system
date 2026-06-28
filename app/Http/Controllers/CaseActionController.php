<?php

namespace App\Http\Controllers;

use App\Models\StudentCase;
use App\Models\CaseAction;
use Illuminate\Http\Request;

class CaseActionController extends Controller
{
    /**
     * Store a new OSA action/intervention for a case.
     */
    public function store(Request $request, StudentCase $case)
    {
        $this->authorize('recordAction', $case);

        $request->validate([
            'action_type' => 'required|in:letter_sent,counseling,parent_conference,verbal_warning,written_warning,other',
            'description' => 'required|string|max:2000',
        ]);

        CaseAction::create([
            'case_id'                => $case->id,
            'user_id'                => auth()->id(),
            'action_type'            => $request->action_type,
            'description'            => $request->description,
            'endorsed_to_grievance'  => false,
        ]);

        return back()->with('success', 'OSA action recorded successfully.');
    }

    /**
     * Endorse a case to the Grievance Committee.
     * For major offenses, requires at least 1 prior OSA action.
     */
    public function endorse(Request $request, StudentCase $case)
    {
        $this->authorize('endorse', $case);

        $request->validate([
            'description' => 'nullable|string|max:2000',
        ]);

        // Guard: major offenses need at least 1 prior OSA action
        if ($case->isMajorOffense() && !$case->canEndorseToGrievance()) {
            return back()->with('error', 'Cannot endorse a major offense to the Grievance Committee without first documenting at least one OSA action (e.g., letter sent, counseling, parent conference).');
        }

        // Create the endorsement action
        CaseAction::create([
            'case_id'                => $case->id,
            'user_id'                => auth()->id(),
            'action_type'            => 'endorsement',
            'description'            => $request->description ?? 'Case officially endorsed to the Grievance Committee.',
            'endorsed_to_grievance'  => true,
        ]);

        // Note: Status no longer changes to "Endorsed to Grievance". 
        // It remains Pending (or current status) until a hearing is scheduled.
        $case->update([
            'endorsed_at' => now(),
        ]);

        return back()->with('success', 'Case has been officially endorsed to the Grievance Committee.');
    }
}
