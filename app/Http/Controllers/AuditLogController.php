<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->can('view_audit_logs'), 403);
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);
        $logs = AuditLog::with('user')->when($request->filled('q'), fn ($q) => $q->where(fn ($sub) => $sub->where('action', 'like', '%'.$request->q.'%')->orWhere('route_name', 'like', '%'.$request->q.'%')->orWhere('ip_address', 'like', '%'.$request->q.'%')))->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))->latest('created_at')->paginate(30)->withQueryString();
        return view('dashboard.governance.audit', compact('logs', 'filters'));
    }
}
