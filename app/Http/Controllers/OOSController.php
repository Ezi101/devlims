<?php

namespace App\Http\Controllers;

use App\OOS;
use App\AuditLog;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OOSController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('OOS.view')) {
            abort(403, 'Unauthorized action.');
        }

        $oos = OOS::where('business_id', Auth::user()->business_id)->get();
        $oosLogs = AuditLog::where('module', 'OOS')->orderBy('created_at', 'desc')->get();

        return view('oos.index', compact('oos', 'oosLogs'))->with('success', 'OOS list retrieved successfully.');
    }

    public function create()
    {
        if (!auth()->user()->can('OOS.create')) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();

        return view('oos.create', compact('user'))->with('success', 'OOS creation form loaded successfully.');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('OOS.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'product_name' => 'nullable',
                'reason' => 'nullable',
            ]);


            $user = Auth::user();
            $businessId = $user->business_id;
            $data = $request->all();
            $data['business_id'] = $businessId;
            $oos = $user->oos()->create($data);

            // Log creation event
            AuditLogger::log('created', 'OOS', 'OOS ID: ' . $oos->id);

            return redirect()->route('oos.index')->with('status', ['success' => 1, 'msg' => __('method.oos_created')]);
        } catch (\Exception $e) {
            return redirect()->route('oos.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function edit(OOS $oos)
    {
        if (!auth()->user()->can('OOS.edit')) {
            abort(403, 'Unauthorized action.');
        }

        return view('oos.edit', compact('oos'))->with('success', 'OOS edit form loaded successfully.');
    }

    public function update(Request $request, OOS $oos)
    {
        if (!auth()->user()->can('OOS.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (!$oos) {
                abort(404);
            }

            $request->validate([
                'product_name' => 'nullable',
                'reason' => 'nullable',
            ]);

            $oldValues = $oos->only(['product_name', 'reason']);
            $oos->update($request->all());
            $newValues = $oos->only(['product_name', 'reason']);
            $fieldNames = [
                'product_name' => 'Name',
                'reason' => 'Reason'
            ];
            $changes = [];
            foreach ($newValues as $key => $newValue) {
                if ($oldValues[$key] != $newValue) {
                    $fieldName = $fieldNames[$key] ?? ucfirst($key); // Use friendly name or default to key
                    $changes[] = " <b>{$fieldName}:</b> from <b> '{$oldValues[$key]}' </b>to <b>'{$newValue}'</b>";
                }
            }
            $changesDetails = implode(' | ', $changes);
            $logMessage = "<b>OOS ID: " . $oos->id . "</b> was <b>updated:</b><br>" . $changesDetails;
            AuditLogger::log('updated', 'OOS', $logMessage);

            return redirect()->route('oos.index')->with('status', ['success' => 1, 'msg' => __('method.oos_updated')]);
        } catch (\Exception $e) {
            return redirect()->route('oos.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function destroy(OOS $oos)
    {
        if (!auth()->user()->can('OOS.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {


            // Log deletion event before actually deleting
            $oosId = $oos->id;
            $oos->delete();
            AuditLogger::log('deleted', 'OOS', 'OOS ID: ' . $oosId);

            return redirect()->route('oos.index')->with('status', ['success' => 1, 'msg' => __('method.oos_deleted')]);
        } catch (\Exception $e) {
            return redirect()->route('oos.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function show(OOS $oos)
    {
        if (!$oos) {
            abort(404);
        }

        return view('oos.show', compact('oos'))->with('success', 'OOS details retrieved successfully.');
    }

    public function reply(Request $request, OOS $oos)
    {
        try {
            $request->validate([
                'response' => 'nullable',
            ]);
            $oosId = $oos->id;

            $oos->update(['response' => $request->input('response')]);
            AuditLogger::log('responded', 'OOS', '<b>OOS ID: ' . $oos->id . '</b> was <b>responded</b> as [<b>' . $oos->response . '</b>]');
            return redirect()->route('oos.show', $oos->id)->with('status', ['success' => 1, 'msg' => __('method.oos_response_added')]);
        } catch (\Exception $e) {
            return redirect()->route('oos.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }
}
