<?php

namespace App\Http\Controllers;

use App\Complaint;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('complaints.view')) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();

        $complaints = Complaint::where('business_id', Auth::user()->business_id)->get();

        return view('complaints.index', compact('user', 'complaints'))->with('success', 'Complaints retrieved successfully.');
    }

    public function create()
    {
        if (!auth()->user()->can('Complaints.create')) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();
        $complaints = Complaint::where('business_id', Auth::user()->business_id)->get();

        return view('complaints.create', compact('user', 'complaints'))->with('success', 'Complaint creation form loaded successfully.');
    }


    public function store(Request $request)
    {
        if (!auth()->user()->can('Complaints.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'user_id' => 'required',
                'status' => 'required',
                'assigned_to' => 'required',
                'subject' => 'required',
                'description' => 'required',
            ]);
            $user = Auth::user();
            $businessId = $user->business_id;
            $complaint = Complaint::create([
                'user_id' => $request->user_id,
                'business_id' => $businessId,
                'status' => $request->status,
                'assigned_to' => $request->assigned_to,
                'subject' => $request->subject,
                'description' => $request->description,
                'complaint_date' => now(),
            ]);

            // Log creation event
            AuditLogger::log('created', 'Complaint', 'Complaint ID: ' . $complaint->id . ' & Subject: ' . $complaint->subject);

            return redirect()->route('complaints.index')->with('status', ['success' => 1, 'msg' => __('method.complaint_added')]);
        } catch (\Exception $e) {
            return redirect()->route('complaints.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }



    public function edit(Complaint $complaint)
    {
        if (!auth()->user()->can('Complaints.edit')) {
            abort(403, 'Unauthorized action.');
        }

        return view('complaints.edit', compact('complaint'))->with('success', 'Complaint editing form loaded successfully.');
    }

    public function update(Request $request, Complaint $complaint)
    {
        if (!auth()->user()->can('Complaints.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'subject' => 'required',
                'description' => 'required',
            ]);

            // Capture the old values
            $oldValues = $complaint->only(['subject', 'description']);

            // Update the complaint data
            $complaint->update([
                'subject' => $request->subject,
                'description' => $request->description,
            ]);

            // Capture the new values
            $newValues = $complaint->only(['subject', 'description']);
            $fieldNames = [
                'subject' => 'Subject',
                'description' => 'Description',

            ];
            $changes = [];
            foreach ($newValues as $key => $newValue) {
                if ($oldValues[$key] != $newValue) {
                    $fieldName = $fieldNames[$key] ?? ucfirst($key); // Use friendly name or default to key
                    $changes[] = " <b>{$fieldName}:</b> from <b> '{$oldValues[$key]}' </b>to <b>'{$newValue}'</b>";
                }
            }
            $changesDetails = implode(' | ', $changes);
            $logMessage = "<b>Complaint ID: " . $complaint->id . "</b> was <b>updated:</b><br>" . $changesDetails;

            // Log the update event
            AuditLogger::log('updated', 'Complaint', $logMessage);

            return redirect()->route('complaints.index')->with('status', ['success' => 1, 'msg' => __('method.complaint_updated')]);
        } catch (\Exception $e) {
            return redirect()->route('complaints.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function destroy(Complaint $complaint)
    {
        if (!auth()->user()->can('Complaints.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Log deletion event before actually deleting
            $complaintId = $complaint->id;
            $complaint->delete();
            AuditLogger::log('deleted', 'Complaint', 'Complaint ID: ' . $complaintId);

            return redirect()->route('complaints.index')->with('status', ['success' => 1, 'msg' => __('method.complaint_deleted')]);
        } catch (\Exception $e) {
            return redirect()->route('complaints.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function reply(Request $request, Complaint $complaint)
    {
        try {
            $request->validate([
                'response' => 'required',
            ]);

            $complaint->update(['response' => $request->input('response')]);

            // Log response event
            AuditLogger::log('responded', 'Complaint', '<b>Complaint ID: ' . $complaint->id . '</b> was <b>responded</b> as [<b>' . $complaint->response . '</b>]');

            return redirect()->route('complaints.show', $complaint->id)->with('status', ['success' => 1, 'msg' => __('method.complaint_response_added')]);
        } catch (\Exception $e) {
            return redirect()->route('complaints.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }
    public function show(Complaint $complaint)
    {
        return view('complaints.show', compact('complaint'))->with('success', 'Complaint details retrieved successfully.');
    }
}
