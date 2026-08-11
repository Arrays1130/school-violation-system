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
     * Major offenses are auto-endorsed on create; this remains for manual/minor cases.
     */
    public function endorse(Request $request, StudentCase $case)
    {
        $this->authorize('endorse', $case);

        if ($reason = $case->endorseBlockReason()) {
            return back()->with('error', $reason);
        }

        $request->validate([
            'description' => 'nullable|string|max:2000',
        ]);

        $case->endorseToGrievance(
            auth()->id(),
            $request->description ?: 'Case officially endorsed to the Grievance Committee.'
        );

        return back()->with('success', 'Case has been officially endorsed to the Grievance Committee.');
    }
}
