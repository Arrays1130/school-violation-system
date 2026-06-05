<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // Only Super Admin and Admin can access this
        abort_unless($request->user()->isSuperAdmin() || $request->user()->isAdmin(), 403, 'Unauthorized access to audit logs.');

        $query = Activity::with(['causer', 'subject'])->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        $logs = $query->paginate(20)->appends($request->all());

        return view('reports.audit-logs', compact('logs'));
    }
}
