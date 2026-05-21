<?php

namespace App\Http\Controllers;

use App\PTR;
use App\STR;
use App\AuditLog;
use App\Feedback;
use App\Transaction;
use GuzzleHttp\Client;
use App\SampleAndTests;
use App\BusinessLocation;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('feedback.view')) {
            abort(403, 'Unauthorized action.');
        }

        $feedbacks = Feedback::where('business_id', Auth::user()->business_id)->get();

        return view('feedbacks.index', compact('feedbacks'))->with('success', 'Feedbacks retrieved successfully.');
    }

    public function create()
    {
        if (!auth()->user()->can('feedback.create')) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();

        return view('feedbacks.create', compact('user'))->with('success', 'Feedback creation form loaded successfully.');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('feedback.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'title' => 'required',
                'description' => 'nullable',
                'rating' => 'nullable|integer|min:1|max:5',
            ]);

            $user = Auth::user();
            $businessId = $user->business_id;

            // Add 'business_id' to the data array
            $data = $request->all();
            $data['business_id'] = $businessId;

            $feedback = $user->feedbacks()->create($data);

            // Log creation event
            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'created',
                'module' => 'Feedback',
                'details' => 'Feedback ID: ' . $feedback->id . ' & Title: ' . $feedback->title,
            ]);

            return redirect()->route('feedbacks.index')->with('status', ['success' => 1, 'msg' => __('method.feedback_created')]);
        } catch (\Exception $e) {
            return redirect()->route('feedbacks.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function edit(Feedback $feedback)
    {
        if (!auth()->user()->can('feedback.edit')) {
            abort(403, 'Unauthorized action.');
        }

        return view('feedbacks.edit', compact('feedback'))->with('success', 'Feedback editing form loaded successfully.');
    }

    public function update(Request $request, Feedback $feedback)
    {
        if (!auth()->user()->can('feedback.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (!$feedback) {
                abort(404);
            }

            $request->validate([
                'title' => 'required',
                'description' => 'nullable',
                'rating' => 'nullable|integer|min:1|max:5',
            ]);
            $oldValues = $feedback->only(['title', 'description', 'rating']);


            // Update the feedback
            $feedback->update($request->all());
            $newValues = $feedback->only(['title', 'description', 'rating']);
            $fieldNames = [
                'title' => 'Title',
                'description' => 'Description',
                'rating' => 'Rating',

            ];
            // Generate the details of changes
            foreach ($newValues as $key => $newValue) {
                if ($oldValues[$key] != $newValue) {
                    $fieldName = $fieldNames[$key] ?? ucfirst($key); // Use friendly name or default to key
                    $changes[] = " <b>{$fieldName}:</b> from <b> '{$oldValues[$key]}' </b> to <b>'{$newValue}'</b>";
                }
            }
            $changesDetails = implode(' | ', $changes);
            $logMessage = "<b>Feedback ID: " . $feedback->id . "</b> was <b>updated:</b><br>" . $changesDetails;
            // Log update event with changes details
            AuditLogger::log('updated', 'Feedback', $logMessage);


            return redirect()->route('feedbacks.index')->with('status', ['success' => 1, 'msg' => __('method.feedback_updated')]);
        } catch (\Exception $e) {
            return redirect()->route('feedbacks.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }


    public function destroy(Feedback $feedback)
    {
        if (!auth()->user()->can('feedback.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (!$feedback) {
                abort(404);
            }

            // Log deletion event
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'deleted',
                'module' => 'Feedback',
                'details' => 'Feedback ID: ' . $feedback->id,
            ]);

            $feedback->delete();

            return redirect()->route('feedbacks.index')->with('status', ['success' => 1, 'msg' => __('method.feedback_deleted')]);
        } catch (\Exception $e) {
            return redirect()->route('feedbacks.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function show(Feedback $feedback)
    {
        if (!$feedback) {
            abort(404);
        }

        return view('feedbacks.show', compact('feedback'))->with('success', 'Feedback details retrieved successfully.');
    }

    // public function sendWhatsAppMessage()
    // {
    //     $business_id = request()->session()->get('user.business_id');

    //     $afmsl_location = BusinessLocation::where('business_id', $business_id)
    //         ->where('name', 'like', '%' . 'afmsl' . '%')
    //         ->first();
    //     $samplesReceivedToday = Transaction::where('business_id', $business_id)->where('location_id', $afmsl_location->id)->where('status', 'Received by AFMSL')->where('product_type', 'sample')->distinct('product_id')->where('type', 'purchase')->whereDate('created_at', now()->toDateString())->count();
    //     $ptrsCreatedToday = PTR::whereDate('created_at', now()->toDateString())->count();
    //     $strsCreatedToday = STR::whereDate('created_at', now()->toDateString())->count();
    //     $testsPerformedToday = SampleAndTests::whereDate('created_at', now()->toDateString())->count();
    //     $phoneNumber = '923331570071'; // Default phone number
    //     $message = "
    //     *Daily Report - " . now()->format('d M Y') . "*
        
    //     - Samples Received: *$samplesReceivedToday*
    //     - PTRs Created: *$ptrsCreatedToday*
    //     - STRs Created: *$strsCreatedToday*
    //     - Tests Performed: *$testsPerformedToday*
        
    //     Thank you.
    // ";





    //     $client = new Client();
    //     $url = 'https://mkt.eziline.com/api/send';
    //     $instanceId = '66E176C028D01';
    //     $accessToken = '6635c54d988b3'; // Access token in the body

    //     try {
    //         $response = $client->post($url, [
    //             'headers' => [
    //                 'Content-Type' => 'application/json',
    //             ],
    //             'json' => [
    //                 'number' => (int)$phoneNumber, // Send as an integer
    //                 'type' => 'text', // Type of message
    //                 'message' => $message, // Message body
    //                 'instance_id' => $instanceId, // Instance ID
    //                 'access_token' => $accessToken, // Access token in the body
    //             ]
    //         ]);

    //         $responseBody = json_decode($response->getBody(), true);
    //         return response()->json($responseBody);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()]);
    //     }
    // }
}
