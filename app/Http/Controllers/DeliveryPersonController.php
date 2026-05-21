<?php

namespace App\Http\Controllers;

use App\DeliveryPerson;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryPersonController extends Controller
{
    public function index()
    {

        $deliveryPersons = DeliveryPerson::where('business_id', Auth::user()->business_id)->get();

        return view('delivery_persons.index', compact('deliveryPersons'));
    }

    public function create()
    {
        return view('delivery_persons.create');
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        try {

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'cnic' => 'required|string|max:15',
                'phone' => 'required|string|max:20',
                'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $validatedData['business_id'] = $business_id; // Ensure the business_id is correctly added to validated data

            if ($request->hasFile('picture')) {
                $validatedData['picture'] = $request->file('picture')->store('img');
            }

            $dp = DeliveryPerson::create($validatedData);
            AuditLogger::log('created', 'Delivery Person', 'Delivery Person ID: ' . $dp->id . ' & Name:' . $dp->name);


            return redirect()->route('delivery_persons.index')->with('status', ['success' => 1, 'msg' => __('method.delivery_person_created')]);
        } catch (\Exception $e) {
            return redirect()->route('delivery_persons.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }


    public function show(DeliveryPerson $deliveryPerson)
    {
        return view('delivery_persons.show', compact('deliveryPerson'));
    }

    public function edit(DeliveryPerson $deliveryPerson)
    {
        return view('delivery_persons.edit', compact('deliveryPerson'));
    }

    public function update(Request $request, DeliveryPerson $deliveryPerson)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'cnic' => 'required|string|max:15|unique:delivery_persons,cnic,' . $deliveryPerson->id,
                'phone' => 'required|string|max:20',
                'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            // Capture the old values
            $oldValues = $deliveryPerson->only(['name', 'cnic', 'phone']);

            $data = $request->all();

            if ($request->hasFile('picture')) {
                $data['picture'] = $request->file('picture')->store('pictures', 'public');
            }

            // Update the delivery person details
            $deliveryPerson->update($data);

            // Capture the new values
            $newValues = $deliveryPerson->only(['name', 'cnic', 'phone']);
            $fieldNames = [
                'name' => 'Name',
                'cnic' => 'CNIC No.',
                'phone' => 'Contact No.',
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
            $logMessage = "<b>Delivery Person ID: " . $deliveryPerson->id . "</b> was <b>updated:</b><br>" . $changesDetails;
            // Log the update action
            AuditLogger::log('updated', 'Delivery Person', $logMessage);

            return redirect()->route('delivery_persons.index')->with('status', ['success' => 1, 'msg' => __('method.delivery_person_updated')]);
        } catch (\Exception $e) {
            return redirect()->route('delivery_persons.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function destroy(DeliveryPerson $deliveryPerson)
    {
        try {
            AuditLogger::log('deleted', 'Delivery Person', 'Delivery Person ID: ' . $deliveryPerson->id);

            $deliveryPerson->delete();
            return redirect()->route('delivery_persons.index')->with('status', ['success' => 1, 'msg' => __('method.delivery_person_deleted')]);
        } catch (\Exception $e) {
            return redirect()->route('delivery_persons.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }


    public function storeQuick(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        try {

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'cnic' => 'required|string|max:15',
                'phone' => 'required|string|max:20',
                'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $validatedData['business_id'] = $business_id;

            if ($request->hasFile('picture')) {
                $validatedData['picture'] = $request->file('picture')->store('img');
            }

            $deliveryPersonCreated = DeliveryPerson::create($validatedData);
            AuditLogger::log('created', 'Delivery Person', 'Delivery Person ID: ' . $deliveryPersonCreated->id);


            return response()->json([
                'success' => true,
                'msg' => __('method.contract_created'),
                'delivery_person_id' => $deliveryPersonCreated->id,
                'delivery_person_name' => $deliveryPersonCreated->name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __($e),

            ]);
        }
    }
}
