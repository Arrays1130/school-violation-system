<?php



namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;

use App\Models\Hearing;

use App\Models\Student;

use Carbon\Carbon;

use Illuminate\Http\Request;



class HearingController extends Controller

{

    public function upcoming(Request $request)

    {

        $user = $request->user();



        if (! $user->isDean() && ! $user->isSuperAdmin()) {

            abort(403, 'Unauthorized.');

        }



        $studentIds = Student::query()

            ->when($user->isDean() && ! $user->isSuperAdmin(), fn ($query) => $query->forDeanDepartment($user->department))

            ->pluck('id');



        $hearings = Hearing::query()

            ->whereHas('case', fn ($q) => $q->whereIn('student_id', $studentIds))

            ->where('scheduled_at', '>=', now()->startOfDay())

            ->with(['case.student', 'case.violation'])

            ->orderBy('scheduled_at')

            ->limit(60)

            ->get()

            ->map(fn (Hearing $hearing) => [

                'id' => $hearing->id,

                'scheduled_at' => $hearing->scheduled_at?->toIso8601String(),

                'venue' => $hearing->venue,

                'student_name' => $hearing->case?->student?->full_name,

                'department' => $hearing->case?->student?->department,

                'violation_title' => $hearing->case?->violation?->title,

                'case_id' => $hearing->case_id,

                'case_status' => $hearing->case?->status,

            ]);



        return response()->json(['hearings' => $hearings]);

    }



    public function calendar(Request $request)

    {

        $user = $request->user();



        if (! $user->isDean() && ! $user->isSuperAdmin() && ! $user->isAdmin()) {

            abort(403, 'Unauthorized.');

        }



        $month = $request->string('month')->toString() ?: now()->format('Y-m');

        $start = Carbon::parse($month.'-01')->startOfMonth();

        $end = (clone $start)->endOfMonth();



        $query = Hearing::query()

            ->whereBetween('scheduled_at', [$start, $end])

            ->with(['case.student', 'case.violation'])

            ->orderBy('scheduled_at');



        if ($user->isDean() && ! $user->isSuperAdmin()) {

            $query->whereHas('case.student', fn ($q) => $q->forDeanDepartment($user->department));

        }



        $hearings = $query->get()->map(fn (Hearing $hearing) => [

            'id' => $hearing->id,

            'scheduled_at' => $hearing->scheduled_at?->toIso8601String(),

            'venue' => $hearing->venue,

            'student_name' => $hearing->case?->student?->full_name,

            'violation_title' => $hearing->case?->violation?->title,

            'case_id' => $hearing->case_id,

        ]);



        return response()->json(['month' => $month, 'hearings' => $hearings]);

    }

}


