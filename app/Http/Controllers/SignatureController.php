<?php

namespace App\Http\Controllers;

use App\User;
use App\Signature;
use App\AuditLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignatureController extends Controller
{
    public function index()
    {

        if (!auth()->user()->can('Signatures.view')) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();
        $users = User::all();

        $signatures = Signature::where('business_id', Auth::user()->business_id)->get();

        return view('signatures.index', compact('signatures', 'users', 'user'))->with('success', 'Signatures retrieved successfully.');
    }

    public function userSignature()
    {
        $user = Auth::user();
        $userSignature = Signature::where('employee_id', $user->id)->first();

        return view('signatures.user', compact('userSignature'))->with('success', 'User signature retrieved successfully.');
    }

    public function create()
    {
        if (!auth()->user()->can('Signatures.create')) {
            abort(403, 'Unauthorized action.');
        }

        $users = User::all();

        return view('signatures.create', compact('users'))->with('success', 'Signature creation form loaded successfully.');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('Signatures.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'employee_id' => 'required|string',
                'designation' => 'required|string',
            ]);
            // Assuming $user contains the authenticated user
            $user = Auth::user();
            $businessId = $user->business_id;


            // Check if a signature already exists for the submitted employee ID
            $existingSignature = Signature::where('employee_id', $validatedData['employee_id'])->first();
            if ($existingSignature) {
                return redirect()->route('signatures.index')->with('status', ['success' => 0, 'msg' => __('messages.signature_exists'),]);
            }

            // Attempt to create the signature
            $uniqueSignature = $this->generateUniqueSignature();
            $signature = Signature::create([
                'user_id' => auth()->user()->id,
                'business_id' => $businessId,
                'name' => $validatedData['name'],
                'employee_id' => $validatedData['employee_id'],
                'designation' => $validatedData['designation'],
                'unique_signature' => $uniqueSignature,
            ]);

            // Log creation event
            AuditLog::create([
                'user_id' => auth()->user()->id,
                'event' => 'created',
                'module' => 'Signature',
                'details' => 'Signature ID: ' . $signature->id . ' for ' . $signature->name,
            ]);

            return redirect()->route('signatures.index')->with('status', ['success' => 1, 'msg' => __('method.signature_created'),]);
        } catch (\Exception $e) {
            return redirect()->route('signatures.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong'),]);
        }
    }

    public function edit(Signature $signature)
    {
        if (!auth()->user()->can('Signatures.edit')) {
            abort(403, 'Unauthorized action.');
        }

        return view('signatures.edit', compact('signature'))->with('success', 'Signature editing form loaded successfully.');
    }

    public function update(Request $request, Signature $signature)
    {
        if (!auth()->user()->can('Signatures.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'employee_id' => 'required|string',
                'designation' => 'required|string',
            ]);

            $signature->update([
                // 'business_id' => $businessId,
                'name' => $validatedData['name'],
                'employee_id' => $validatedData['employee_id'],
                'designation' => $validatedData['designation'],
            ]);

            // Log update event
            AuditLog::create([
                'user_id' => auth()->user()->id,
                'event' => 'updated',
                'module' => 'Signature',
                'details' => 'Signature ID: ' . $signature->id,
            ]);

            return redirect()->route('signatures.index')->with('status', ['success' => 1, 'msg' => __('method.signature_updated'),]);
        } catch (\Exception $e) {
            return redirect()->route('signatures.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong'),]);
        }
    }

    public function destroy(Signature $signature)
    {
        if (!auth()->user()->can('Signatures.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Log deletion event before actually deleting
            $signatureId = $signature->id;
            $signature->delete();

            // Log deletion event
            AuditLog::create([
                'user_id' => auth()->user()->id,
                'event' => 'deleted',
                'module' => 'Signature',
                'details' => 'Signature ID: ' . $signatureId,
            ]);

            return redirect()->route('signatures.index')->with('status', ['success' => 1, 'msg' => __('method.signature_deleted'),]);
        } catch (\Exception $e) {
            return redirect()->route('signatures.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong'),]);
        }
    }

    private function generateUniqueSignature()
    {
        $startLetter = strtoupper(substr(str_shuffle('ABDEFGHJKLMNPQRSTWY'), 0, 1));
        $digits = str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
        $middleLetter = strtoupper(substr(str_shuffle('ABDEFGHJKLMNPQRSTWY'), 0, 1));
        $rawSignature = $startLetter . substr($digits, 0, 2) . $middleLetter . substr($digits, 2, 2);
        $checksum = 0;
        foreach (str_split($rawSignature) as $char) {
            $checksum += ord($char);
        }
        $checksum = strtoupper(dechex($checksum % 36)); 
        $uniqueSignature = $rawSignature . $checksum;

        return $uniqueSignature;
    }

    public function userSignatureByEmployeeId($employeeId)
    {
        return Signature::where('employee_id', $employeeId)->first();
    }
}
