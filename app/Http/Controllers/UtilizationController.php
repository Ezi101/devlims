<?php

namespace App\Http\Controllers;

use App\AuditLog;
use App\Batch;
use App\Instruments;
use App\Product;
use App\Transaction;
use App\Utilization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UtilizationController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('Devices.Utilizations.view')) {
            abort(403, 'Unauthorized action.');
        }

        $utilizations = Utilization::where('business_id', Auth::user()->business_id)->get();
        $devices = Instruments::where('business_id', Auth::user()->business_id)->get();
        $batches = Batch::where('business_id', Auth::user()->business_id)->get();
        $issueIds = Transaction::where('business_id', Auth::user()->business_id)->where('type', 'sell')->where('product_type', 'sample')
            ->pluck('invoice_no')
            ->unique();
        $products = Product::where('business_id', Auth::user()->business_id)->get();
        $samples = Product::where('business_id', Auth::user()->business_id)->get();
        return view('utilizations.index', compact('utilizations', 'devices', 'batches', 'products', 'samples', 'issueIds'));
    }
    public function create()
    {
        if (!auth()->user()->can('Devices.Utilizations.add')) {
            abort(403, 'Unauthorized action.');
        }

        $utilizations = Utilization::where('business_id', Auth::user()->business_id)->get();
        $devices = Instruments::where('business_id', Auth::user()->business_id)->get();
        $batches = Batch::where('business_id', Auth::user()->business_id)->get();
        $issueIds = Transaction::where('business_id', Auth::user()->business_id)->where('type', 'sell')->where('product_type', 'sample')
            ->pluck('invoice_no')
            ->unique();
        $products = Product::where('business_id', Auth::user()->business_id)->get();
        $samples = Product::where('business_id', Auth::user()->business_id)->get();
        return view('utilizations.create', compact('utilizations', 'devices', 'batches', 'products', 'samples', 'issueIds'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('Devices.Utilizations.add')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $validatedData = $request->validate([
                'utilization_start_time' => 'nullable|date',
                'utilization_end_time' => 'nullable|date',
                'apparatus_status' => 'required|in:okay,not_okay',
                'sample_name' => 'nullable|string',
                'sample_number' => 'nullable|string',
                'rpm' => 'required|integer',
                'apparatus_used_name' => 'required|string',
                'cleaning_start_time' => 'nullable|date',
                'cleaning_end_time' => 'nullable|date',
                'performed_by' => 'nullable|integer',
                'device_id' => 'required|exists:instruments,id',
                'product_id' => 'nullable|exists:products,id',

            ]);

            $user = Auth::user();
            $businessId = $user->business_id;

            // Create utilization record
            $utilization = new Utilization($validatedData);
            $utilization->business_id = $businessId;
            $utilization->save();

            // Log creation event
            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'created',
                'module' => 'Utilization',
                'details' => 'Utilization ID: ' . $utilization->id,
            ]);
            return redirect('equipment')->with('status', ['success' => 1, 'msg' => __('method.utilization_created')]);
        } catch (\Exception $e) {
            return redirect('equipment')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }


    public function show(Utilization $utilization)
    {
        $device = Instruments::find($utilization->device_id);

        return view('utilizations.show', compact('utilization', 'device'));
    }


    public function edit(Utilization $utilization)
    {
        if (!auth()->user()->can('Devices.Utilizations.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $devices = Instruments::where('business_id', Auth::user()->business_id)->get();
        $samples = Product::where('business_id', Auth::user()->business_id)->get();
        $batches = Batch::where('business_id', Auth::user()->business_id)->get();
        return view('utilizations.edit', compact('utilization', 'devices', 'samples', 'batches'));
    }

    public function update(Request $request, Utilization $utilization)
    {
        if (!auth()->user()->can('Devices.Utilizations.edit')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $validatedData = $request->validate([
                'utilization_start_time' => 'nullable|date',
                'utilization_end_time' => 'nullable|date',
                'apparatus_status' => 'required|in:okay,not_okay',
                'sample_name' => 'nullable|string',
                'sample_number' => 'nullable|string',
                'rpm' => 'required|integer',
                'apparatus_used_name' => 'required|string',
                'cleaning_start_time' => 'nullable|date',
                'cleaning_end_time' => 'nullable|date',
                'performed_by' => 'nullable|integer',
                'device_id' => 'required|exists:instruments,id',
                'product_id' => 'nullable|exists:products,id',

            ]);
            $utilization->update($validatedData);
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'module' => 'Utilization',
                'details' => 'Utilization ID: ' . $utilization->id,
            ]);
            return redirect('equipment')->with('status', ['success' => 1, 'msg' => __('method.utilization_updated')]);
        } catch (\Exception $e) {
            return redirect('equipment')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function destroy(Utilization $utilization)
    {
        if (!auth()->user()->can('Devices.Utilizations.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $utilization->delete();
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'deleted',
                'module' => 'Utilization',
                'details' => 'Utilization ID: ' . $utilization->id,
            ]);
            return redirect('equipment')->with('status', ['success' => 1, 'msg' => __('method.utilization_deleted')]);
        } catch (\Exception $e) {
            return redirect('equipment')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }
    public function getProductDetails($productId)
    {
        $user = Auth::user();
        $business_id = $user->business_id;

        $issueIds = Transaction::where('business_id', $business_id)->where('product_id', $productId)->where('type', 'sell')->get();

        $data = [
            'issueIds' => $issueIds->toArray()
        ];

        return response()->json($data);
    }
    public function getBatchDetails($issueId)
    {
        $user = Auth::user();
        $business_id = $user->business_id;
        $batches = Transaction::where('business_id', $business_id)->where('invoice_no', $issueId)
            ->distinct('batch_no')
            ->pluck('batch_no')
            ->toArray();

        $batchDetails = Batch::whereIn('id', $batches)->get();

        return response()->json(['batches' => $batchDetails]);
    }
}
