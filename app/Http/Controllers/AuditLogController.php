<?php

namespace App\Http\Controllers;

use App\User;
use App\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index($module = null)
    {
        if (!auth()->user()->can('activity_log.view')) {
            abort(403, 'Unauthorized action.');
        }

        $modules = AuditLog::select('module')->distinct()->orderBy('module')->pluck('module');
        $actions = AuditLog::select('event')->distinct()->orderBy('id')->pluck('event');
        $users = User::all()->map(function ($user) {
            return $user->getUserFullNameAttribute();
        });

        // Update the logs query to handle multiple modules
        if ($module) {
            $moduleArray = explode(',', $module); // Handle multiple modules if they are comma-separated
            $logs = AuditLog::whereIn('module', array_map('ucfirst', $moduleArray)) // Capitalize each module name
                ->orderBy('created_at', 'desc')
                ->paginate(500);
        } else {
            $logs = AuditLog::orderBy('created_at', 'desc') ->paginate(500);
        }

        return view('logs.index', compact('logs', 'modules', 'users', 'actions'));
    }

    public function destroy($id)
    {
        $log = AuditLog::findOrFail($id);

        $log->delete();

        return redirect()->back()->with('success', 'Audit log entry has been deleted successfully.');
    }
}
