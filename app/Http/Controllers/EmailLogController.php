<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', \App\Models\EmailLog::class);

        $query = \App\Models\EmailLog::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest()->paginate(20);

        $logs->getCollection()->transform(function ($log) {
            return array_merge($log->toArray(), [
                'created_at_date' => $log->created_at->format('M d, Y'),
                'created_at_time' => $log->created_at->format('h:i A'),
            ]);
        });

        return inertia('Reports/EmailLogs', [
            'logs' => $logs,
            'filters' => $request->only('search'),
        ]);
    }
    public function destroy(\App\Models\EmailLog $emailLog)
    {
        $this->authorize('delete', $emailLog);

        $emailLog->delete();
        return back()->with('success', 'Email record deleted successfully.');
    }
}
