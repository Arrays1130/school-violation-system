<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseAction;
use App\Models\CaseAttachment;
use App\Models\StudentCase;
use Illuminate\Http\Request;
use App\Support\AttachmentStorage;

class MobileCaseController extends Controller
{
    public function acknowledge(Request $request, StudentCase $case)
    {
        $case->loadMissing('student');
        $this->authorize('acknowledge', $case);

        $user = $request->user();

        CaseAction::create([
            'case_id' => $case->id,
            'user_id' => $user->id,
            'action_type' => 'other',
            'description' => 'Acknowledged by '.$user->name.' via VioTrack mobile.',
            'endorsed_to_grievance' => false,
        ]);

        return response()->json(['message' => 'Case acknowledged successfully.']);
    }

    public function downloadAttachment(Request $request, CaseAttachment $attachment)
    {
        $user = $request->user();
        $case = $attachment->case()->with('student')->firstOrFail();

        if ($user->isDean()) {
            $longName = \App\Models\Student::resolveDepartmentLongName($user->department);
            if ($case->student->department !== $longName && $case->student->department !== $user->department) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        if (! AttachmentStorage::disk()->exists($attachment->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return AttachmentStorage::disk()->download($attachment->file_path, $attachment->file_name);
    }
}
