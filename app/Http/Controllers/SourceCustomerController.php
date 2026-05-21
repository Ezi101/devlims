<?php

namespace App\Http\Controllers;

use App\SourceCustomer;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SourceCustomerController extends Controller
{
    public function index()
    {

        $sourceCustomers = SourceCustomer::where('business_id', Auth::user()->business_id)->get();
        return view('sources_custom.index', compact('sourceCustomers'));
    }

    public function create()
    {
        return view('sources_custom.create');
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        try {

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'city' => 'nullable|string|max:30',
                'phone' => 'required|string|max:20',
            ]);

            $validatedData['business_id'] = $business_id;


            $sourceCustomer = SourceCustomer::create($validatedData);
            AuditLogger::log('created', 'Source', 'Source ID: ' . $sourceCustomer->id . ' & Name: ' . $sourceCustomer->name);


            return redirect()->route('source.index')->with('status', ['success' => 1, 'msg' => __('method.source_created')]);
        } catch (\Exception $e) {
            return redirect()->route('source.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }


    public function show(SourceCustomer $sourceCustomer)
    {
        return view('sources_custom.show', compact('sourceCustomer'));
    }

    public function edit(SourceCustomer $sourceCustomer)
    {
        return view('sources_custom.edit', compact('sourceCustomer'));
    }

    public function update(Request $request, SourceCustomer $sourceCustomer)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'city' => 'nullable|string|max:20',
            ]);

            // Capture the old values
            $oldValues = $sourceCustomer->only(['name', 'phone', 'city']);


            $data = $request->all();



            $sourceCustomer->update($data);
            $newValues = $sourceCustomer->only(['name', 'phone', 'city']);
            $fieldNames = [
                'name' => 'Name',
                'phone' => 'Phone',
                'city' => 'City',
            ];            $changes = [];
            foreach ($newValues as $key => $newValue) {
                if ($oldValues[$key] != $newValue) {
                    $fieldName = $fieldNames[$key] ?? ucfirst($key); // Use friendly name or default to key
                    $changes[] = " <b>{$fieldName}:</b> from <b> '{$oldValues[$key]}' </b>to <b>'{$newValue}'</b>";
                }
            }
            $changesDetails = implode(' | ', $changes);
            $logMessage = "<b>Source ID: " . $sourceCustomer->id . "</b> was <b>updated:</b><br>" . $changesDetails;
            // Log the update event
            AuditLogger::log('updated', 'Source', $logMessage);

            return redirect()->route('source.index')->with('status', ['success' => 1, 'msg' => __('method.source_updated')]);
        } catch (\Exception $e) {
            return redirect()->route('source.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function destroy(SourceCustomer $sourceCustomer)
    {
        // dd($sourceCustomer->id);
        try {
            $sourceCustomer->delete();
            AuditLogger::log('deleted', 'Source', 'Source ID: ' . $sourceCustomer->id);

            return redirect()->route('source.index')->with('status', ['success' => 1, 'msg' => __('method.source_deleted')]);
        } catch (\Exception $e) {
            return redirect()->route('source.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }


    public function storeQuick(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        try {

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'city' => 'nullable|string|max:20',
            ]);

            $validatedData['business_id'] = $business_id;

            $sourceCustomerCreated = SourceCustomer::create($validatedData);
            AuditLogger::log('created', 'Source', 'Source ID: ' . $sourceCustomerCreated->id);


            return response()->json([
                'success' => true,
                'msg' => __('method.source_created'),
                'source_customer_id' => $sourceCustomerCreated->id,
                'source_customer_name' => $sourceCustomerCreated->name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __($e),

            ]);
        }
    }
}
