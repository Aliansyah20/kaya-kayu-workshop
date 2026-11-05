<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest('created_at');

        if ($request->filled('q')) {
            $search = $request->get('q');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%");
            });
        }

        if ($request->filled('module')) {
            $query->where('module', $request->get('module'));
        }

        if ($request->filled('user')) {
            $query->where('user_id', $request->get('user'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->get('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->get('to'));
        }

        $logs = $query->paginate(25)->withQueryString();
        $modules = ActivityLog::query()->select('module')->distinct()->orderBy('module')->pluck('module');
        $users = User::orderBy('name')->get();

        return view('activity-logs.index', compact('logs', 'modules', 'users'));
    }
}
