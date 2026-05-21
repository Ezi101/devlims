<?php

namespace App\Http\Controllers;

use App\AuditLog;
use App\Deviation;
use App\Instruments;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviationController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('Deviations.view')) {
            abort(403, 'Unauthorized action.');
        }

        $deviations = Deviation::where('business_id', Auth::user()->business_id)->get();
        $devices = Instruments::where('business_id', Auth::user()->business_id)->get();

        return view('deviations.index', compact('deviations', 'devices'))->with('success', 'Deviations retrieved successfully.');
    }

    public function create($id = null)
    {
        if (!auth()->user()->can('Deviations.create')) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();
        if ($id) {
            $device = Instruments::findOrFail($id); // Will throw 404 if not found
            $devices[] = $device; // Put into array for dropdown
        } else {
            $devices = Instruments::where('business_id', Auth::user()->business_id)->get();
        }
        return view('deviations.create', compact('user', 'devices'))->with('success', 'Deviation creation form loaded successfully.');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('Deviations.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'type' => 'required|string',
                'description' => 'nullable|string',
                'device_id' => 'required|exists:instruments,id', // Changed from 'equipment'
                // Remove these if not needed or add them to your form
                // 'sample_id' => 'required',
                // 'test_id' => 'required',
                // 'batch_no' => 'required',
                // 'lab' => 'required',
            ]);

            $user = Auth::user();
            $businessId = $user->business_id;

            $deviation = $user->deviations()->create([
                'type' => $request->input('type'),
                'business_id' => $businessId,
                'description' => $request->input('description'),
                'deviation_date' => now(),
                'device_id' => $request->input('device_id'), // Changed from 'equipment'
                // Only include these if they're in your form
                // 'lab' => $request->input('lab'),
                // 'batch_id' => $request->input('batch_no'),
                // 'test_id' => $request->input('test_id'),
                // 'sample_id' => $request->input('sample_id'),
            ]);

            // Log creation event
            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'created',
                'module' => 'Deviation',
                'details' => 'Deviation ID: ' . $deviation->id,
            ]);

            return redirect()->back()
                ->with('status', ['success' => 1, 'msg' => __('method.deviation_created')]);
        } catch (\Exception $e) {
            \Log::error('Deviation creation error: ' . $e->getMessage());
            return redirect()->back()
                ->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')])
                ->withInput();
        }
    }


    public function edit(Deviation $deviation)
    {

        if (!auth()->user()->can('Deviations.edit')) {
            abort(403, 'Unauthorized action.');
        }

        return view('deviations.edit', compact('deviation'))->with('success', 'Deviation editing form loaded successfully.');
    }

    public function update(Request $request, Deviation $deviation)
    {
        if (!auth()->user()->can('Deviations.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (!$deviation) {
                abort(404);
            }

            $request->validate([
                'type' => 'nullable',
                'description' => 'nullable',
            ]);

            $deviation->update([
                'type' => $request->input('type'),
                'description' => $request->input('description'),
            ]);

            // Log update event
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'module' => 'Deviation',
                'details' => 'Deviation ID: ' . $deviation->id,
            ]);

            return redirect()->back()->with('status', ['success' => 1, 'msg' => __('method.deviation_updated')]);
        } catch (\Exception $e) {
            return redirect()->back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }


    public function destroy(Deviation $deviation)
    {
        if (!auth()->user()->can('Deviations.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {


            // Log deletion event
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'deleted',
                'module' => 'Deviation',
                'details' => 'Deviation ID: ' . $deviation->id,
            ]);

            $deviation->delete();

            return redirect()->back()->with('status', ['success' => 1, 'msg' => __('method.deviation_deleted')]);
        } catch (\Exception $e) {
            return redirect()->back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }


    public function show(Deviation $deviation)
    {
        if (!$deviation) {
            abort(404);
        }

        return view('deviations.show', compact('deviation'))->with('success', 'Deviation details loaded successfully.');
    }

    public function reply(Request $request, Deviation $deviation)
    {
        try {
            $request->validate([
                'response' => 'required',
            ]);

            $deviation->update(['response' => $request->input('response')]);

            // Log response event
            AuditLogger::log('responded', 'Deviation', '<b>Deviation ID: ' . $deviation->id . '</b> was <b>responded</b> as [<b>' . $deviation->response . '</b>]');

            return redirect()->route('deviations.show', $deviation->id)->with('status', ['success' => 1, 'msg' => __('method.deviation_response_added')]);
        } catch (\Exception $e) {
            return redirect()->route('deviations.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }
}
