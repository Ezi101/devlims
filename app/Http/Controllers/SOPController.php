<?php

namespace App\Http\Controllers;

use App\SOP;
use App\Product;
use App\AuditLog;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SOPController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('SOPs.view')) {
            abort(403, 'Unauthorized action.');
        }

        $sops = SOP::where('business_id', Auth::user()->business_id)->get();
        return view('sops.index', compact('sops'));
    }

    public function create()
    {
        if (!auth()->user()->can('SOPs.create')) {
            abort(403, 'Unauthorized action.');
        }

        // $samples = Product::where('business_id', Auth::user()->business_id)->get();

        return view('sops.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('SOPs.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'sop_expiry_date' => 'required|date',
                'description' => 'required',
                'user_id' => 'required|exists:users,id',
                'file' => 'nullable|file|max:10240'
            ]);

            $data = $request->all();
            $data['reference_code'] = $this->generateReferenceNumber();
            $user = Auth::user();
            $data['business_id'] = $user->business_id;

            if ($request->hasFile('file')) {
                $data['file'] = $request->file('file')->store('img');
            }

            $sop = SOP::create($data);

            // Log creation event
            AuditLogger::log('created', 'SOP', 'SOP ID: ' . $sop->id . ' & Name: ' . $sop->title);

            return redirect()->route('sops.index')->with('status', ['success' => 1, 'msg' => __('method.sop_created')]);
        } catch (\Exception $e) {
            return redirect()->route('sops.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }


    public function edit($id)
    {
        if (!auth()->user()->can('SOPs.edit')) {
            abort(403, 'Unauthorized action.');
        }


        // $samples = Product::where('business_id', Auth::user()->business_id)->get();
        $sop = SOP::findOrFail($id);
        return view('sops.edit', compact('sop'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('SOPs.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'sop_expiry_date' => 'required|date',
                'description' => 'required',
                'user_id' => 'required|exists:users,id',
                'file' => 'nullable|file|max:10240'
            ]);

            $sop = SOP::findOrFail($id);

            // Capture old values
            $oldValues = $sop->only(['title', 'category', 'sop_expiry_date', 'description']);

            // Update SOP details
            $sop->title = $request->input('title');
            $sop->category = $request->input('category');
            $sop->sop_expiry_date = $request->input('sop_expiry_date');
            $sop->description = $request->input('description');
            $sop->user_id = $request->input('user_id');
            $sop->reference_code = $request->input('reference_code');

            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($sop->file) {
                    Storage::delete($sop->file);
                }

                // Store new file
                $sop->file = $request->file('file')->store('img');
            }

            $sop->save();

            // Capture new values
            $newValues = $sop->only(['title', 'category', 'sop_expiry_date', 'description']);
            $fieldNames = [
                'title' => 'Title',
                'category' => 'Category',
                'sop_expiry_date' => 'Expiry Date',
                'description' => 'Description',
            ];
            // Prepare the log details
            $changes = [];
            foreach ($newValues as $key => $newValue) {
                if ($oldValues[$key] != $newValue) {
                    $fieldName = $fieldNames[$key] ?? ucfirst($key); // Use friendly name or default to key
                    $changes[] = " <b>{$fieldName}:</b> from <b> '{$oldValues[$key]}' </b>to <b>'{$newValue}'</b>";
                }
            }
            $changesDetails = implode(' | ', $changes);
            $logMessage = "<b>SOP ID: " . $sop->id . "</b> was <b>updated:</b><br>" . $changesDetails;
            // Log the update action
            AuditLogger::log('updated', 'SOP', $logMessage);

            return redirect()->route('sops.index')->with('status', ['success' => 1, 'msg' => __('method.sop_updated')]);
        } catch (\Exception $e) {
            return redirect()->route('sops.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }



    public function destroy(SOP $sop)
    {
        if (!auth()->user()->can('SOPs.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $sop->delete();

            // Log deletion event
            AuditLogger::log('deleted', 'SOP', 'SOP ID: ' . $sop->id);

            return redirect()->route('sops.index')->with('status', ['success' => 1, 'msg' => __('method.sop_deleted')]);
        } catch (\Exception $e) {
            return redirect()->route('sops.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function show($id)
    {
        $sop = SOP::findOrFail($id);
        return view('sops.show', compact('sop'));
    }

    private function generateReferenceNumber()
    {
        return 'REF' . mt_rand(10000, 99999);
    }
}
