<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Only administrators can view audit logs.');
        }

        $query = AuditLog::with('user')
            ->orderByDesc('created_at');

        // Filter by event type
        if ($request->filled('event_type')) {
            $query->where('event', $request->event_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $auditLogs = $query->paginate(20);

        // Get stats for the dashboard
        $stats = [
            'total_events' => AuditLog::count(),
            'unique_users' => AuditLog::distinct('user_id')->count('user_id'),
            'events_today' => AuditLog::whereDate('created_at', today())->count(),
            'critical_events' => AuditLog::whereIn('event', ['deleted', 'access_revoked', 'login_failed'])->count()
        ];

        // Get event types for filtering
        $eventTypes = AuditLog::distinct()->pluck('event');

        return Inertia::render('Admin/Audit/Index', [
            'auditLogs' => $auditLogs,
            'stats' => $stats,
            'eventTypes' => $eventTypes,
            'filters' => $request->only(['event_type', 'user_id', 'date_from', 'date_to', 'search'])
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Only administrators can view audit logs.');
        }

        $auditLog->load('user');

        return Inertia::render('Admin/Audit/Show', [
            'auditLog' => $auditLog
        ]);
    }
}