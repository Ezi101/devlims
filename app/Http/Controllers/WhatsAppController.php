<?php

namespace App\Http\Controllers;

use App\PTR;
use App\STR;
use App\Batch;
use App\Product;
use Carbon\Carbon;
use App\Transaction;
use App\SampleReading;
use App\WhatsAppSetting;
use App\BusinessLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends Controller
{
    public function showForm()
    {
        if (!auth()->user()->can('others.access_whatsapp_module')) {
            abort(403, 'Unauthorized action.');
        }
        $afmslsettings = WhatsAppSetting::where('department', 'afmsl')->get();
        $afimssettings = WhatsAppSetting::where('department', 'afims')->get();

        $afmslrecipientsData = $afmslsettings->map(function ($setting) {
            return [
                'id' => $setting->id,
                'number' => json_decode($setting->recipients, true)[0] ?? '',
                'modules' => json_decode($setting->modules, true) ?? [],
            ];
        });
        $afimsrecipientsData = $afimssettings->map(function ($setting) {
            return [
                'id' => $setting->id,

                'number' => json_decode($setting->recipients, true)[0] ?? '',
                'modules' => json_decode($setting->modules, true) ?? [],
            ];
        });

        return view('whatsapp.form', compact('afmslrecipientsData', 'afimsrecipientsData', 'afmslsettings', 'afimssettings'));
    }

    public function saveSettings(Request $request)
    {
        try {
            $request->validate([
                'app_key' => 'required|string',
                'auth_key' => 'required|string',
                'recipients' => 'required|array',
                'department' => 'required|string',

            ]);

            foreach ($request->recipients as $recipient) {
                if (!isset($recipient['number']) || empty($recipient['number'])) {
                    continue; // Skip if the recipient number is empty
                }

                // Find existing record with the given number
                $existingSetting = WhatsAppSetting::where('department', 'afmsl')->whereJsonContains('recipients', $recipient['number'])->first();

                if ($existingSetting) {
                    // Update existing record
                    $existingSetting->update([
                        'app_key' => $request->app_key,
                        'auth_key' => $request->auth_key,
                        'modules' => isset($recipient['modules'])
                            ? json_encode($recipient['modules'])
                            : json_encode([]), // Save selected modules or an empty array
                    ]);
                } else {
                    // Create a new record
                    WhatsAppSetting::create([
                        'app_key' => $request->app_key,
                        'auth_key' => $request->auth_key,
                        'department' => $request->department,
                        'recipients' => json_encode([$recipient['number']]), // Wrap number in an array before encoding
                        'modules' => isset($recipient['modules'])
                            ? json_encode($recipient['modules'])
                            : json_encode([]), // Save selected modules or an empty array
                    ]);
                }
            }

            return redirect()->back()->with('status', ['success' => 1, 'msg' => __('Settings saved successfully.')]);
        } catch (\Exception $e) {
            return redirect()->back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }
    public function saveafimsSettings(Request $request)
    {
        try {
            $request->validate([
                'app_key' => 'required|string',
                'auth_key' => 'required|string',
                'recipients' => 'required|array',
                'department' => 'required|string',

            ]);

            foreach ($request->recipients as $recipient) {
                if (!isset($recipient['number']) || empty($recipient['number'])) {
                    continue; // Skip if the recipient number is empty
                }

                // Find existing record with the given number
                $existingSetting = WhatsAppSetting::where('department', 'afims')->whereJsonContains('recipients', $recipient['number'])->first();

                if ($existingSetting) {
                    // Update existing record
                    $existingSetting->update([
                        'app_key' => $request->app_key,
                        'auth_key' => $request->auth_key,
                        'modules' => isset($recipient['modules'])
                            ? json_encode($recipient['modules'])
                            : json_encode([]), // Save selected modules or an empty array
                    ]);
                } else {
                    // Create a new record
                    WhatsAppSetting::create([
                        'app_key' => $request->app_key,
                        'auth_key' => $request->auth_key,
                        'department' => $request->department,

                        'recipients' => json_encode([$recipient['number']]), // Wrap number in an array before encoding
                        'modules' => isset($recipient['modules'])
                            ? json_encode($recipient['modules'])
                            : json_encode([]), // Save selected modules or an empty array
                    ]);
                }
            }

            return redirect()->back()->with('status', ['success' => 1, 'msg' => __('Settings saved successfully.')]);
        } catch (\Exception $e) {
            return redirect()->back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }
    public function updateStatus(Request $request)
    {
        $department = $request->input('department');
        $status = $request->input('status');

        // Update all records for the given department
        $updated = WhatsAppSetting::where('department', $department)->update(['status' => $status]);

        return response()->json(['success' => $updated > 0]);
    }




    public function sendMessage()
    {
        // Retrieve all settings
        $afmslsettings = WhatsAppSetting::where('department', 'afmsl')->get();
        $afimssettings = WhatsAppSetting::where('department', 'afims')->get();
        $afmslSettingsforStatus = WhatsAppSetting::where('department', 'afmsl')->first();
        $afimsSettingsforStatus = WhatsAppSetting::where('department', 'afims')->first();
        if ($afmslSettingsforStatus && $afmslSettingsforStatus->status === 'active') {


            if ($afmslsettings->isEmpty()) {
                \Log::error('WhatsApp settings not found.');
                return "Settings not found!";
            }

            // Prepare a map of recipients and their corresponding modules/messages
            $afmslrecipientMap = [];

            foreach ($afmslsettings as $setting) {
                $appkey = $setting->app_key;
                $authkey = $setting->auth_key;

                // Decode recipient and modules
                $recipients = json_decode($setting->recipients, true);
                $modules = json_decode($setting->modules, true);

                if (empty($recipients) || empty($modules)) {
                    continue; // Skip if no recipients or modules are configured
                }

                foreach ($recipients as $recipient) {
                    if (!isset($afmslrecipientMap[$recipient])) {
                        $afmslrecipientMap[$recipient] = [
                            'modules' => [],
                            'app_key' => $appkey,
                            'auth_key' => $authkey,
                        ];
                    }

                    // Merge modules without duplicating
                    $afmslrecipientMap[$recipient]['modules'] = array_unique(
                        array_merge($afmslrecipientMap[$recipient]['modules'], $modules)
                    );
                }
            }

            // Iterate over the recipient map and send messages
            foreach ($afmslrecipientMap as $recipient => $data) {
                $message = "Good Morning,\n\nHere's a quick summary of yesterday's activities:\n\n";
                $today = Carbon::now();

                if ($today->isMonday()) {
                    $startTime = (clone $today)->subDays(3)->setTime(7, 0, 0); // Friday at 7 AM
                    $endTime = (clone $today)->subDays(2)->setTime(7, 0, 0); // Saturday at 7 AM
                } else {
                    $startTime = (clone $today)->subDay()->setTime(7, 0, 0); // Previous day at 7 AM
                    $endTime = (clone $today)->setTime(7, 0, 0); // Today at 7 AM
                }




                $business_id = 15 ?? '15';
                $afmsl_location = BusinessLocation::where('business_id', $business_id)
                    ->where('name', 'like', '%' . 'afmsl' . '%')
                    ->first();

                // **Samples Section**
                if (in_array('Received Sample', $data['modules'])) {
                    $recievedSamples = Transaction::where('business_id', $business_id)
                        ->where('location_id', $afmsl_location->id)
                        ->where('status', 'Received by AFMSL')
                        ->where('product_type', 'sample')
                        ->where('type', 'purchase')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->count();

                    $message .= "=== *Samples* ===\n";
                    $message .= "- *Received*: $recievedSamples\n\n";
                }

                // **PTR Section**
                if (in_array('PTR', $data['modules'])) {
                    // Fetch sample product count
                    $SamplesForPtrTotal = Product::where('business_id', $business_id)
                        ->where('product_type', 'sample')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('name')
                        ->count();

                    // Fetch approved PTR count
                    $ptrsApprovedCount = PTR::where('business_id', $business_id)
                        ->where('status', 'approved')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('ptr_no')
                        ->count();

                    // Calculate uncreated PTRs
                    $ptrsUncreatedCount = max(0, $SamplesForPtrTotal - $ptrsApprovedCount);

                    // Append PTR message
                    $message .= "=== *PTR* ===\n";
                    $message .= "- *Pending*: $ptrsUncreatedCount\n";
                    $message .= "- *Approved*: $ptrsApprovedCount\n\n";
                }


                // **Tests Section**
                if (in_array('Tests', $data['modules'])) {
                    $tests = SampleReading::where('business_id', $business_id)
                        ->distinct('test')
                        ->get();

                    $completedTests = $tests->where('status', 'completed')->whereBetween('updated_at', [$startTime, $endTime])
                        ->count();
                    $pendingTests = $tests->where('status', 'not_started')->whereBetween('updated_at', [$startTime, $endTime])
                        ->count();

                    $message .= "=== *Test Status* ===\n";
                    $message .= "- *Queued*: $pendingTests\n";
                    $message .= "- *Completed*: $completedTests\n\n";
                }

                // **STR Section**
                if (in_array('STR', $data['modules'])) {
                    // Retrieve received sample IDs
                    $receivedSamplesIdsArray = Transaction::where([
                        ['business_id', $business_id],
                        ['location_id', $afmsl_location->id],
                        ['product_type', 'sample'],
                        ['type', 'purchase']
                    ])->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('product_id')
                        ->pluck('product_id')
                        ->toArray();

                    // Batch-wise counts
                    $strsTotalCountBatchwise = Batch::whereIn('sample_id', $receivedSamplesIdsArray)
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('id')
                        ->count();

                    $strsPendingCountBatchwise = max(0, $strsTotalCountBatchwise - STR::where('business_id', $business_id)
                        ->where('status', 'approved')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('batch_no')
                        ->count());

                    // Sample-wise counts
                    $strsTotalCountSamplewise = Product::whereIn('id', $receivedSamplesIdsArray)
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('name')
                        ->count();

                    $strsPendingCountSamplewise = max(0, $strsTotalCountSamplewise - STR::where('business_id', $business_id)
                        ->where('status', 'approved')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('sample_id')
                        ->count());

                    // Approved count (common for both sample-wise and batch-wise)
                    $strsApprovedCountBatchwise = STR::where('business_id', $business_id)
                        ->where('status', 'approved')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('batch_no')
                        ->count();
                    $strsApprovedCountSamplewise = STR::where('business_id', $business_id)
                        ->where('status', 'approved')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('sample_id')
                        ->count();

                    // Build the message
                    $message .= "=== *STR* ===\n";
                    $message .= "- *Pending*: $strsPendingCountSamplewise\n";
                    // $message .= "- *Pending (Batch)*: $strsPendingCountBatchwise\n";
                    $message .= "- *Approved*: $strsApprovedCountSamplewise\n\n";
                    // $message .= "- *Approved (Batch)*: $strsApprovedCountBatchwise\n\n";
                }






                // **Total Data Section**
                $receivedSamplesIdsArray = Transaction::where([
                    ['business_id', $business_id],
                    ['location_id', $afmsl_location->id],
                    ['product_type', 'sample'],
                    ['status', 'Received by AFMSL'],
                    ['type', 'purchase']
                ])->distinct('product_id')
                    ->pluck('product_id')
                    ->toArray();

                $totalReceivedSamples = Transaction::where([
                    ['business_id', $business_id],
                    ['location_id', $afmsl_location->id],
                    ['product_type', 'sample'],
                    ['status', 'Received by AFMSL'],
                    ['type', 'purchase']
                ])->distinct('product_id')->count();

                $totalSamplesMedicine = Product::where('business_id', $business_id)
                    ->where('product_type', 'sample')
                    ->distinct('name')
                    ->whereHas('product_locations', function ($query) {
                        $query->where('product_locations.location_id', 5); // Specify pivot table
                    })->count();

                $totalPtrApproved = PTR::where('business_id', $business_id)
                    ->distinct('sample_id')
                    ->count();

                // Batch-wise counts
                $strsTotalCountBatchwise = Batch::whereIn('sample_id', $receivedSamplesIdsArray)
                    ->distinct('id')
                    ->count();

                $strsPendingCountBatchwise = max(0, $strsTotalCountBatchwise - STR::where('business_id', $business_id)
                    ->where('status', 'approved')
                    ->distinct('batch_no')
                    ->count());

                // Sample-wise counts
                $strsTotalCountSamplewise = Product::whereIn('id', $receivedSamplesIdsArray)
                    ->distinct('name')
                    ->count();


                // Approved count (common for both sample-wise and batch-wise)
                $strsApprovedCountBatchwise = STR::where('business_id', $business_id)
                    ->where('status', 'approved')
                    ->distinct('batch_no')
                    ->count();
                $strsApprovedCountSamplewise = STR::where('business_id', $business_id)
                    ->where('status', 'approved')
                    ->distinct('sample_id')
                    ->count();
                $totalTests = SampleReading::where('business_id', $business_id)
                    ->distinct('test')
                    ->count();

                $completedTests = SampleReading::where('business_id', $business_id)
                    ->where('status', 'completed')
                    ->distinct('test')
                    ->count();

                $totalDataMessage = ""; // Initialize empty message for total data section

                if (in_array('Received Sample', $data['modules'])) {
                    $totalDataMessage .= "- *Samples*: $totalReceivedSamples/$totalSamplesMedicine\n";
                }

                if (in_array('PTR', $data['modules'])) {
                    $totalDataMessage .= "- *PTRs*: $totalPtrApproved/$totalSamplesMedicine\n";
                }

                if (in_array('STR', $data['modules'])) {
                    $totalDataMessage .= "- *STRs*: $strsApprovedCountSamplewise/$strsTotalCountSamplewise\n";
                }

                if (in_array('Tests', $data['modules'])) {
                    $totalDataMessage .= "- *Tests*: $completedTests/$totalTests\n\n";
                }

                // Check if there’s any data to show before displaying the section
                if (!empty($totalDataMessage)) {
                    $message .= "\n\n=== *Total Data* ===\n";
                    $message .= "(Rcd/Total | Appv/Total | Comp/Total)\n";
                    $message .= $totalDataMessage;
                    $message .= "---------------------------------\n";
                    $message .= "For any queries, feel free to contact us.\n\nBest regards,\nTeam Eziline";
                }

                $url = "https://ezimk.com/wa/public/api/create-message";
                // Send message to the recipient
                $payload = [
                    'appkey'  => $data['app_key'],
                    'authkey' => $data['auth_key'],
                    'to'      => $recipient,
                    'message' => $message,
                ];

                $response = Http::asForm()->post($url, $payload);
                if ($response->successful()) {
                    \Log::info("Message sent to $recipient: " . $response->body());
                } else {
                    \Log::error("Failed to send message to $recipient: " . $response->body());
                }
                sleep(15);
            }
        }



        if ($afimsSettingsforStatus && $afimsSettingsforStatus->status === 'active') {

            if ($afimssettings->isEmpty()) {
                \Log::error('WhatsApp settings not found.');
                return "afims Settings not found!";
            }

            // Prepare a map of recipients and their corresponding modules/messages
            $afimsrecipientMap = [];

            foreach ($afimssettings as $setting) {
                $appkey = $setting->app_key;
                $authkey = $setting->auth_key;

                // Decode recipient and modules
                $recipients = json_decode($setting->recipients, true);
                $modules = json_decode($setting->modules, true);

                if (empty($recipients) || empty($modules)) {
                    continue; // Skip if no recipients or modules are configured
                }

                foreach ($recipients as $recipient) {
                    if (!isset($afimsrecipientMap[$recipient])) {
                        $afimsrecipientMap[$recipient] = [
                            'modules' => [],
                            'app_key' => $appkey,
                            'auth_key' => $authkey,
                        ];
                    }

                    // Merge modules without duplicating
                    $afimsrecipientMap[$recipient]['modules'] = array_unique(
                        array_merge($afimsrecipientMap[$recipient]['modules'], $modules)
                    );
                }
            }

            // Iterate over the recipient map and send messages
            foreach ($afimsrecipientMap as $recipient => $data) {
                $message = "Good Morning,\n\nHere's a quick summary of yesterday's activities:\n\n";

                $today = Carbon::now();

                if ($today->isMonday()) {
                    $startTime = (clone $today)->subDays(3)->setTime(7, 0, 0); // Friday at 7 AM
                    $endTime = (clone $today)->subDays(2)->setTime(7, 0, 0); // Saturday at 7 AM
                } else {
                    $startTime = (clone $today)->subDay()->setTime(7, 0, 0); // Previous day at 7 AM
                    $endTime = (clone $today)->setTime(7, 0, 0); // Today at 7 AM
                }




                $business_id = 15 ?? '15';
                $afmsl_location = BusinessLocation::where('business_id', $business_id)
                    ->where('name', 'like', '%' . 'afmsl' . '%')
                    ->first();




                if (in_array('Samples Collected', $data['modules'])) {
                    $collectedSamples = Transaction::where('business_id', $business_id)
                        ->where('location_id', $afmsl_location->id)
                        ->where('status', 'Forwarded to 2IC')
                        ->where('product_type', 'sample')
                        ->where('type', 'purchase')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->count();

                    $message .= "=== *Samples* ===\n";
                    $message .= "- *Collected*: $collectedSamples\n\n";
                }
                if (in_array('Samples Forwarded', $data['modules'])) {
                    $forwardedSamples = Transaction::where('business_id', $business_id)
                        ->where('location_id', $afmsl_location->id)
                        ->where('status', 'Forward by AFIMS')
                        ->where('product_type', 'sample')
                        ->where('type', 'purchase')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->count();

                    $message .= "=== *Samples* ===\n";
                    $message .= "- *Forwarded*: $forwardedSamples\n\n";
                }
                if (in_array('Samples Draft', $data['modules'])) {
                    $draftSamples = Transaction::where('business_id', $business_id)
                        ->where('location_id', $afmsl_location->id)
                        ->where('status', 'draft')
                        ->where('product_type', 'sample')
                        ->where('type', 'purchase')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->count();

                    $message .= "=== *Samples* ===\n";
                    $message .= "- *Draft*: $draftSamples\n\n";
                }






                $totalSamplesMedicineforafimsData = Product::where('business_id', $business_id)
                    ->where('product_type', 'sample')
                    ->distinct('name')
                    ->whereHas('product_locations', function ($query) {
                        $query->where('product_locations.location_id', 5); // Specify pivot table
                    })->count();

                $totalSamplesCollected = Transaction::where('business_id', $business_id)
                    ->where('location_id', $afmsl_location->id)
                    ->where('status', 'Forwarded to 2IC')
                    ->where('product_type', 'sample')
                    ->where('type', 'purchase')
                    ->count();
                $totalSamplesForwarded = Transaction::where('business_id', $business_id)
                    ->where('location_id', $afmsl_location->id)
                    ->where('status', 'Forward by AFIMS')
                    ->where('product_type', 'sample')
                    ->where('type', 'purchase')
                    ->count();
                $totalSamplesDraft = Transaction::where('business_id', $business_id)
                    ->where('location_id', $afmsl_location->id)
                    ->where('status', 'draft')
                    ->where('product_type', 'sample')
                    ->where('type', 'purchase')
                    ->count();



                // **TSR Section**
                if (in_array('TSR', $data['modules'])) {
                    // Get all sample IDs with contract type 'tender'
                    $tenderSampleIds = Transaction::where([
                        ['business_id', $business_id],
                        ['location_id', $afmsl_location->id],
                        ['product_type', 'sample'],
                        ['contract_type', 'tender'],
                        ['type', 'purchase']
                    ])->distinct('product_id')
                        ->pluck('product_id')
                        ->toArray();

                    // Get batch IDs linked to the tender samples
                    $batchIdsBySample = Batch::whereIn('sample_id', $tenderSampleIds)
                        ->select('id', 'sample_id')
                        ->get()
                        ->groupBy('sample_id'); // Group batches by sample ID
                    // Get batch IDs linked to the tender samples


                    $tsrQueued = 0;
                    $tsrPending = 0;
                    $tsrCompleted = 0;

                    foreach ($batchIdsBySample as $sampleId => $batches) {
                        $batchIds = $batches->pluck('id')->toArray();

                        // Get the count of approved STRs for these batches
                        $approvedStrCount = STR::where('business_id', $business_id)
                            ->where('status', 'approved')
                            ->whereIn('batch_no', $batchIds)
                            ->count();

                        $totalBatches = count($batchIds);

                        if ($approvedStrCount === 0) {
                            // No STRs created for any batch → Queued
                            $tsrQueued++;
                        } elseif ($approvedStrCount < $totalBatches) {
                            // Some batches have approved STRs, others are still pending → Pending
                            $tsrPending++;
                        } else {
                            // All batches have approved STRs → Completed
                            $tsrCompleted++;
                        }
                    }

                    $message .= "=== *All TSRs* ===\n";
                    $message .= "- *Queued*: $tsrQueued\n";
                    $message .= "- *Pending*: $tsrPending\n";
                    $message .= "- *Completed*: $tsrCompleted\n\n";
                }
                $tenderSampleIds = Transaction::where([
                    ['business_id', $business_id],
                    ['location_id', $afmsl_location->id],
                    ['product_type', 'sample'],
                    ['contract_type', 'tender'],
                    ['type', 'purchase']
                ])
                    ->distinct('product_id')
                    ->pluck('product_id')
                    ->toArray();
                $batchIdsBySampleWithoutTime = Batch::whereIn('sample_id', $tenderSampleIds)
                    ->select('id', 'sample_id')
                    ->get()
                    ->groupBy('sample_id'); // Group batches by sample ID


                $tsrCompletedWithoutTime = 0;
                $tsrTotalWithoutTime = count($batchIdsBySampleWithoutTime);

                foreach ($batchIdsBySampleWithoutTime as $sampleId => $batches) {
                    $batchIds = collect($batches)->pluck('id')->toArray();
                    $totalBatches = count($batchIds);

                    // Get approved STR count for all time (WITHOUT time filter)
                    $approvedStrCountWithoutTime = STR::where('business_id', $business_id)
                        ->where('status', 'approved')
                        ->whereIn('batch_no', $batchIds)
                        ->count();

                    // Count TSR as completed if all batches have approved STRs
                    if ($approvedStrCountWithoutTime === $totalBatches) {
                        $tsrCompletedWithoutTime++;
                    }
                }

                $totalMessage = ""; // Initialize empty message for total section

                if (in_array('Samples Collected', $data['modules'])) {
                    $totalMessage .= "- *Collected*: $totalSamplesCollected/$totalSamplesMedicineforafimsData\n";
                }

                if (in_array('Samples Forwarded', $data['modules'])) {
                    $totalMessage .= "- *Forwarded*: $totalSamplesForwarded/$totalSamplesMedicineforafimsData\n";
                }

                if (in_array('Samples Draft', $data['modules'])) {
                    $totalMessage .= "- *Draft*: $totalSamplesDraft/$totalSamplesMedicineforafimsData\n";
                }

                if (in_array('TSR', $data['modules'])) {
                    $totalMessage .= "- *TSRs*: $tsrCompletedWithoutTime/$tsrTotalWithoutTime\n";
                }

                if (!empty($totalMessage)) {
                    $message .= "\n\n=== *Total* ===\n";
                    $message .= $totalMessage;
                    $message .= "---------------------------------\n";
                    $message .= "For any queries, feel free to contact us.\n\nBest regards,\nTeam Eziline";
                }


                $url = "https://ezimk.com/wa/public/api/create-message";

                // Send message to the recipient
                $payload = [
                    'appkey'  => $data['app_key'],
                    'authkey' => $data['auth_key'],
                    'to'      => $recipient,
                    'message' => $message,
                ];

                $response = Http::asForm()->post($url, $payload);

                if ($response->successful()) {
                    \Log::info("Message sent to $recipient: " . $response->body());
                } else {
                    \Log::error("Failed to send message to $recipient: " . $response->body());
                }
                sleep(15);
            }
        }



        return "Message sending process completed!";
    }
    public function sendMessageNowManually()
    {
        // Retrieve all settings
        $afmslsettings = WhatsAppSetting::where('department', 'afmsl')->get();
        $afimssettings = WhatsAppSetting::where('department', 'afims')->get();
        $afmslSettingsforStatus = WhatsAppSetting::where('department', 'afmsl')->first();
        $afimsSettingsforStatus = WhatsAppSetting::where('department', 'afims')->first();

        $sentDepartments = [];

        if ($afmslSettingsforStatus && $afmslSettingsforStatus->status === 'active') {

            if ($afmslsettings->isEmpty()) {
                \Log::error('WhatsApp settings not found.');
                return "Settings not found!";
            }

            // Prepare a map of recipients and their corresponding modules/messages
            $afmslrecipientMap = [];

            foreach ($afmslsettings as $setting) {
                $appkey = $setting->app_key;
                $authkey = $setting->auth_key;

                // Decode recipient and modules
                $recipients = json_decode($setting->recipients, true);
                $modules = json_decode($setting->modules, true);

                if (empty($recipients) || empty($modules)) {
                    continue; // Skip if no recipients or modules are configured
                }

                foreach ($recipients as $recipient) {
                    if (!isset($afmslrecipientMap[$recipient])) {
                        $afmslrecipientMap[$recipient] = [
                            'modules' => [],
                            'app_key' => $appkey,
                            'auth_key' => $authkey,
                        ];
                    }

                    // Merge modules without duplicating
                    $afmslrecipientMap[$recipient]['modules'] = array_unique(
                        array_merge($afmslrecipientMap[$recipient]['modules'], $modules)
                    );
                }
            }

            // Iterate over the recipient map and send messages
            foreach ($afmslrecipientMap as $recipient => $data) {
                $message = "Good Morning,\n\nHere's a quick summary of yesterday's activities:\n\n";
                $today = Carbon::now();

                if ($today->isMonday()) {
                    $startTime = (clone $today)->subDays(3)->setTime(7, 0, 0); // Friday at 7 AM
                    $endTime = (clone $today)->subDays(2)->setTime(7, 0, 0); // Saturday at 7 AM
                } else {
                    $startTime = (clone $today)->subDay()->setTime(7, 0, 0); // Previous day at 7 AM
                    $endTime = (clone $today)->setTime(7, 0, 0); // Today at 7 AM
                }




                $business_id = 15 ?? '15';
                $afmsl_location = BusinessLocation::where('business_id', $business_id)
                    ->where('name', 'like', '%' . 'afmsl' . '%')
                    ->first();

                // **Samples Section**
                if (in_array('Received Sample', $data['modules'])) {
                    $recievedSamples = Transaction::where('business_id', $business_id)
                        ->where('location_id', $afmsl_location->id)
                        ->where('status', 'Received by AFMSL')
                        ->where('product_type', 'sample')
                        ->where('type', 'purchase')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->count();

                    $message .= "=== *Samples* ===\n";
                    $message .= "- *Received*: $recievedSamples\n\n";
                }

                // **PTR Section**
                if (in_array('PTR', $data['modules'])) {
                    // Fetch sample product count
                    $SamplesForPtrTotal = Product::where('business_id', $business_id)
                        ->where('product_type', 'sample')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('name')
                        ->count();

                    // Fetch approved PTR count
                    $ptrsApprovedCount = PTR::where('business_id', $business_id)
                        ->where('status', 'approved')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('ptr_no')
                        ->count();

                    // Calculate uncreated PTRs
                    $ptrsUncreatedCount = max(0, $SamplesForPtrTotal - $ptrsApprovedCount);

                    // Append PTR message
                    $message .= "=== *PTR* ===\n";
                    $message .= "- *Pending*: $ptrsUncreatedCount\n";
                    $message .= "- *Approved*: $ptrsApprovedCount\n\n";
                }


                // **Tests Section**
                if (in_array('Tests', $data['modules'])) {
                    $tests = SampleReading::where('business_id', $business_id)
                        ->distinct('test')
                        ->get();

                    $completedTests = $tests->where('status', 'completed')->whereBetween('updated_at', [$startTime, $endTime])
                        ->count();
                    $pendingTests = $tests->where('status', 'not_started')->whereBetween('updated_at', [$startTime, $endTime])
                        ->count();

                    $message .= "=== *Test Status* ===\n";
                    $message .= "- *Queued*: $pendingTests\n";
                    $message .= "- *Completed*: $completedTests\n\n";
                }

                // **STR Section**
                if (in_array('STR', $data['modules'])) {
                    // Retrieve received sample IDs
                    $receivedSamplesIdsArray = Transaction::where([
                        ['business_id', $business_id],
                        ['location_id', $afmsl_location->id],
                        ['product_type', 'sample'],
                        ['type', 'purchase']
                    ])->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('product_id')
                        ->pluck('product_id')
                        ->toArray();

                    // Batch-wise counts
                    $strsTotalCountBatchwise = Batch::whereIn('sample_id', $receivedSamplesIdsArray)
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('id')
                        ->count();

                    $strsPendingCountBatchwise = max(0, $strsTotalCountBatchwise - STR::where('business_id', $business_id)
                        ->where('status', 'approved')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('batch_no')
                        ->count());

                    // Sample-wise counts
                    $strsTotalCountSamplewise = Product::whereIn('id', $receivedSamplesIdsArray)
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('name')
                        ->count();

                    $strsPendingCountSamplewise = max(0, $strsTotalCountSamplewise - STR::where('business_id', $business_id)
                        ->where('status', 'approved')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('sample_id')
                        ->count());

                    // Approved count (common for both sample-wise and batch-wise)
                    $strsApprovedCountBatchwise = STR::where('business_id', $business_id)
                        ->where('status', 'approved')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('batch_no')
                        ->count();
                    $strsApprovedCountSamplewise = STR::where('business_id', $business_id)
                        ->where('status', 'approved')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->distinct('sample_id')
                        ->count();

                    // Build the message
                    $message .= "=== *STR* ===\n";
                    $message .= "- *Pending*: $strsPendingCountSamplewise\n";
                    // $message .= "- *Pending (Batch)*: $strsPendingCountBatchwise\n";
                    $message .= "- *Approved*: $strsApprovedCountSamplewise\n\n";
                    // $message .= "- *Approved (Batch)*: $strsApprovedCountBatchwise\n\n";
                }






                // **Total Data Section**
                $receivedSamplesIdsArray = Transaction::where([
                    ['business_id', $business_id],
                    ['location_id', $afmsl_location->id],
                    ['product_type', 'sample'],
                    ['status', 'Received by AFMSL'],
                    ['type', 'purchase']
                ])->distinct('product_id')
                    ->pluck('product_id')
                    ->toArray();

                $totalReceivedSamples = Transaction::where([
                    ['business_id', $business_id],
                    ['location_id', $afmsl_location->id],
                    ['product_type', 'sample'],
                    ['status', 'Received by AFMSL'],
                    ['type', 'purchase']
                ])->distinct('product_id')->count();

                $totalSamplesMedicine = Product::where('business_id', $business_id)
                    ->where('product_type', 'sample')
                    ->distinct('name')
                    ->whereHas('product_locations', function ($query) {
                        $query->where('product_locations.location_id', 5); // Specify pivot table
                    })->count();

                $totalPtrApproved = PTR::where('business_id', $business_id)
                    ->distinct('sample_id')
                    ->count();

                // Batch-wise counts
                $strsTotalCountBatchwise = Batch::whereIn('sample_id', $receivedSamplesIdsArray)
                    ->distinct('id')
                    ->count();

                $strsPendingCountBatchwise = max(0, $strsTotalCountBatchwise - STR::where('business_id', $business_id)
                    ->where('status', 'approved')
                    ->distinct('batch_no')
                    ->count());

                // Sample-wise counts
                $strsTotalCountSamplewise = Product::whereIn('id', $receivedSamplesIdsArray)
                    ->distinct('name')
                    ->count();


                // Approved count (common for both sample-wise and batch-wise)
                $strsApprovedCountBatchwise = STR::where('business_id', $business_id)
                    ->where('status', 'approved')
                    ->distinct('batch_no')
                    ->count();
                $strsApprovedCountSamplewise = STR::where('business_id', $business_id)
                    ->where('status', 'approved')
                    ->distinct('sample_id')
                    ->count();
                $totalTests = SampleReading::where('business_id', $business_id)
                    ->distinct('test')
                    ->count();

                $completedTests = SampleReading::where('business_id', $business_id)
                    ->where('status', 'completed')
                    ->distinct('test')
                    ->count();

                $totalDataMessage = ""; // Initialize empty message for total data section

                if (in_array('Received Sample', $data['modules'])) {
                    $totalDataMessage .= "- *Samples*: $totalReceivedSamples/$totalSamplesMedicine\n";
                }

                if (in_array('PTR', $data['modules'])) {
                    $totalDataMessage .= "- *PTRs*: $totalPtrApproved/$totalSamplesMedicine\n";
                }

                if (in_array('STR', $data['modules'])) {
                    $totalDataMessage .= "- *STRs*: $strsApprovedCountSamplewise/$strsTotalCountSamplewise\n";
                }

                if (in_array('Tests', $data['modules'])) {
                    $totalDataMessage .= "- *Tests*: $completedTests/$totalTests\n\n";
                }

                // Check if there’s any data to show before displaying the section
                if (!empty($totalDataMessage)) {
                    $message .= "\n\n=== *Total Data* ===\n";
                    $message .= "(Rcd/Total | Appv/Total | Comp/Total)\n";
                    $message .= $totalDataMessage;
                    $message .= "---------------------------------\n";
                    $message .= "For any queries, feel free to contact us.\n\nBest regards,\nTeam Eziline";
                }

                $url = "https://ezimk.com/wa/public/api/create-message";
                // Send message to the recipient
                $payload = [
                    'appkey'  => $data['app_key'],
                    'authkey' => $data['auth_key'],
                    'to'      => $recipient,
                    'message' => $message,
                ];

                $response = Http::asForm()->post($url, $payload);
                if ($response->successful()) {
                    \Log::info("Message sent to $recipient: " . $response->body());
                } else {
                    \Log::error("Failed to send message to $recipient: " . $response->body());
                }
                sleep(15);
            }
            $sentDepartments[] = 'AFMSL';
        }



        if ($afimsSettingsforStatus && $afimsSettingsforStatus->status === 'active') {
            if ($afimssettings->isEmpty()) {
                \Log::error('WhatsApp settings not found.');
                return "afims Settings not found!";
            }

            // Prepare a map of recipients and their corresponding modules/messages
            $afimsrecipientMap = [];

            foreach ($afimssettings as $setting) {
                $appkey = $setting->app_key;
                $authkey = $setting->auth_key;

                // Decode recipient and modules
                $recipients = json_decode($setting->recipients, true);
                $modules = json_decode($setting->modules, true);

                if (empty($recipients) || empty($modules)) {
                    continue; // Skip if no recipients or modules are configured
                }

                foreach ($recipients as $recipient) {
                    if (!isset($afimsrecipientMap[$recipient])) {
                        $afimsrecipientMap[$recipient] = [
                            'modules' => [],
                            'app_key' => $appkey,
                            'auth_key' => $authkey,
                        ];
                    }

                    // Merge modules without duplicating
                    $afimsrecipientMap[$recipient]['modules'] = array_unique(
                        array_merge($afimsrecipientMap[$recipient]['modules'], $modules)
                    );
                }
            }

            // Iterate over the recipient map and send messages
            foreach ($afimsrecipientMap as $recipient => $data) {
                $message = "Good Morning,\n\nHere's a quick summary of yesterday's activities:\n\n";

                $today = Carbon::now();

                if ($today->isMonday()) {
                    $startTime = (clone $today)->subDays(3)->setTime(7, 0, 0); // Friday at 7 AM
                    $endTime = (clone $today)->subDays(2)->setTime(7, 0, 0); // Saturday at 7 AM
                } else {
                    $startTime = (clone $today)->subDay()->setTime(7, 0, 0); // Previous day at 7 AM
                    $endTime = (clone $today)->setTime(7, 0, 0); // Today at 7 AM
                }




                $business_id = 15 ?? '15';
                $afmsl_location = BusinessLocation::where('business_id', $business_id)
                    ->where('name', 'like', '%' . 'afmsl' . '%')
                    ->first();




                if (in_array('Samples Collected', $data['modules'])) {
                    $collectedSamples = Transaction::where('business_id', $business_id)
                        ->where('location_id', $afmsl_location->id)
                        ->where('status', 'Forwarded to 2IC')
                        ->where('product_type', 'sample')
                        ->where('type', 'purchase')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->count();

                    $message .= "=== *Samples* ===\n";
                    $message .= "- *Collected*: $collectedSamples\n\n";
                }
                if (in_array('Samples Forwarded', $data['modules'])) {
                    $forwardedSamples = Transaction::where('business_id', $business_id)
                        ->where('location_id', $afmsl_location->id)
                        ->where('status', 'Forward by AFIMS')
                        ->where('product_type', 'sample')
                        ->where('type', 'purchase')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->count();

                    $message .= "=== *Samples* ===\n";
                    $message .= "- *Forwarded*: $forwardedSamples\n\n";
                }
                if (in_array('Samples Draft', $data['modules'])) {
                    $draftSamples = Transaction::where('business_id', $business_id)
                        ->where('location_id', $afmsl_location->id)
                        ->where('status', 'draft')
                        ->where('product_type', 'sample')
                        ->where('type', 'purchase')
                        ->whereBetween('updated_at', [$startTime, $endTime])
                        ->count();

                    $message .= "=== *Samples* ===\n";
                    $message .= "- *Draft*: $draftSamples\n\n";
                }






                $totalSamplesMedicineforafimsData = Product::where('business_id', $business_id)
                    ->where('product_type', 'sample')
                    ->distinct('name')
                    ->whereHas('product_locations', function ($query) {
                        $query->where('product_locations.location_id', 5); // Specify pivot table
                    })->count();

                $totalSamplesCollected = Transaction::where('business_id', $business_id)
                    ->where('location_id', $afmsl_location->id)
                    ->where('status', 'Forwarded to 2IC')
                    ->where('product_type', 'sample')
                    ->where('type', 'purchase')
                    ->count();
                $totalSamplesForwarded = Transaction::where('business_id', $business_id)
                    ->where('location_id', $afmsl_location->id)
                    ->where('status', 'Forward by AFIMS')
                    ->where('product_type', 'sample')
                    ->where('type', 'purchase')
                    ->count();
                $totalSamplesDraft = Transaction::where('business_id', $business_id)
                    ->where('location_id', $afmsl_location->id)
                    ->where('status', 'draft')
                    ->where('product_type', 'sample')
                    ->where('type', 'purchase')
                    ->count();



                // **TSR Section**
                if (in_array('TSR', $data['modules'])) {
                    // Get all sample IDs with contract type 'tender'
                    $tenderSampleIds = Transaction::where([
                        ['business_id', $business_id],
                        ['location_id', $afmsl_location->id],
                        ['product_type', 'sample'],
                        ['contract_type', 'tender'],
                        ['type', 'purchase']
                    ])->distinct('product_id')
                        ->pluck('product_id')
                        ->toArray();

                    // Get batch IDs linked to the tender samples
                    $batchIdsBySample = Batch::whereIn('sample_id', $tenderSampleIds)
                        ->select('id', 'sample_id')
                        ->get()
                        ->groupBy('sample_id'); // Group batches by sample ID
                    // Get batch IDs linked to the tender samples


                    $tsrQueued = 0;
                    $tsrPending = 0;
                    $tsrCompleted = 0;

                    foreach ($batchIdsBySample as $sampleId => $batches) {
                        $batchIds = $batches->pluck('id')->toArray();

                        // Get the count of approved STRs for these batches
                        $approvedStrCount = STR::where('business_id', $business_id)
                            ->where('status', 'approved')
                            ->whereIn('batch_no', $batchIds)
                            ->count();

                        $totalBatches = count($batchIds);

                        if ($approvedStrCount === 0) {
                            // No STRs created for any batch → Queued
                            $tsrQueued++;
                        } elseif ($approvedStrCount < $totalBatches) {
                            // Some batches have approved STRs, others are still pending → Pending
                            $tsrPending++;
                        } else {
                            // All batches have approved STRs → Completed
                            $tsrCompleted++;
                        }
                    }

                    $message .= "=== *All TSRs* ===\n";
                    $message .= "- *Queued*: $tsrQueued\n";
                    $message .= "- *Pending*: $tsrPending\n";
                    $message .= "- *Completed*: $tsrCompleted\n\n";
                }
                $tenderSampleIds = Transaction::where([
                    ['business_id', $business_id],
                    ['location_id', $afmsl_location->id],
                    ['product_type', 'sample'],
                    ['contract_type', 'tender'],
                    ['type', 'purchase']
                ])
                    ->distinct('product_id')
                    ->pluck('product_id')
                    ->toArray();
                $batchIdsBySampleWithoutTime = Batch::whereIn('sample_id', $tenderSampleIds)
                    ->select('id', 'sample_id')
                    ->get()
                    ->groupBy('sample_id'); // Group batches by sample ID


                $tsrCompletedWithoutTime = 0;
                $tsrTotalWithoutTime = count($batchIdsBySampleWithoutTime);

                foreach ($batchIdsBySampleWithoutTime as $sampleId => $batches) {
                    $batchIds = collect($batches)->pluck('id')->toArray();
                    $totalBatches = count($batchIds);

                    // Get approved STR count for all time (WITHOUT time filter)
                    $approvedStrCountWithoutTime = STR::where('business_id', $business_id)
                        ->where('status', 'approved')
                        ->whereIn('batch_no', $batchIds)
                        ->count();

                    // Count TSR as completed if all batches have approved STRs
                    if ($approvedStrCountWithoutTime === $totalBatches) {
                        $tsrCompletedWithoutTime++;
                    }
                }

                $totalMessage = ""; // Initialize empty message for total section

                if (in_array('Samples Collected', $data['modules'])) {
                    $totalMessage .= "- *Collected*: $totalSamplesCollected/$totalSamplesMedicineforafimsData\n";
                }

                if (in_array('Samples Forwarded', $data['modules'])) {
                    $totalMessage .= "- *Forwarded*: $totalSamplesForwarded/$totalSamplesMedicineforafimsData\n";
                }

                if (in_array('Samples Draft', $data['modules'])) {
                    $totalMessage .= "- *Draft*: $totalSamplesDraft/$totalSamplesMedicineforafimsData\n";
                }

                if (in_array('TSR', $data['modules'])) {
                    $totalMessage .= "- *TSRs*: $tsrCompletedWithoutTime/$tsrTotalWithoutTime\n";
                }

                if (!empty($totalMessage)) {
                    $message .= "\n\n=== *Total* ===\n";
                    $message .= $totalMessage;
                    $message .= "---------------------------------\n";
                    $message .= "For any queries, feel free to contact us.\n\nBest regards,\nTeam Eziline";
                }


                $url = "https://ezimk.com/wa/public/api/create-message";

                // Send message to the recipient
                $payload = [
                    'appkey'  => $data['app_key'],
                    'authkey' => $data['auth_key'],
                    'to'      => $recipient,
                    'message' => $message,
                ];

                $response = Http::asForm()->post($url, $payload);

                if ($response->successful()) {
                    \Log::info("Message sent to $recipient: " . $response->body());
                } else {
                    \Log::error("Failed to send message to $recipient: " . $response->body());
                }
                sleep(15);
            }
            $sentDepartments[] = 'AFIMS';
        }



        return response()->json([
            'success' => !empty($sentDepartments),
            'message' => !empty($sentDepartments)
                ? 'Message sent to ' . implode(' and ', $sentDepartments) . '.'
                : 'No active department found to send messages.'
        ]);
    }


    public function deleteRecipient($id)
    {
        try {
            // Find the recipient entry by ID and delete it
            $recipient = WhatsappSetting::find($id);

            if ($recipient) {
                $recipient->delete();
                return response()->json(['success' => true, 'message' => 'Recipient deleted successfully.']);
            }

            return response()->json(['success' => false, 'message' => 'Recipient not found.']);
        } catch (\Exception $e) {
            \Log::error('Error deleting recipient: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error deleting recipient.'], 500);
        }
    }
}
