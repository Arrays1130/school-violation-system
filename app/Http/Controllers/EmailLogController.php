<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
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

        return view('reports.email-logs', compact('logs'));
    }
    public function destroy(\App\Models\EmailLog $emailLog)
    {
        $emailLog->delete();
        return back()->with('success', 'Email record deleted successfully.');
    }
}
