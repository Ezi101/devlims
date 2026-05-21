<?php

namespace App\Http\Controllers;

use App\Capa;
use App\User;
use App\Product;
use App\AuditLog;
use App\Instruments;
use App\Utilization;
use App\SampleReading;
use App\CalibrationDetail;
use App\Deviation;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class InstrumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('Devices.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user = Auth::user();
        $role = $user->roles->pluck('name')->toArray();
        // Define the target roles
        $targetRoles = [
            'Chemical Lab Manager#' . $business_id,
            'Physical Lab Manager#' . $business_id,
            'Micro Lab Manager#' . $business_id
        ];
        // Check if the user has any of the target roles
        if (count(array_intersect($role, $targetRoles)) > 0) {
            // Get devices based on business ID and role IDs (assuming lab_id is related to roles)
            $devices = Instruments::where('business_id', $user->business_id)
                ->where('lab', $role)
                ->get();
        } else {
            // Get devices based on business ID only
            $devices = Instruments::where('business_id', $user->business_id)
                ->get();
        }
        return view('instrument.index', get_defined_vars());
    }

    public function showDeviceTests($id)
    {
        $device = Instruments::findOrFail($id);
        $tests = SampleReading::where('device_id', $id)->get();

        return view('instrument.showDeviceTests', compact('device', 'tests'));
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('Devices.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        $lab = Role::whereIn('name', [
            'Chemical Lab Manager#15',
            'Physical Lab Manager#15',
            'Micro Lab Manager#15'
        ])->pluck('name', 'name');
        $lab = $lab->map(function ($name) use ($business_id) {
            return str_replace('#' . $business_id, '', $name);
        });


        return view('instrument.create', get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('Devices.create')) {
            abort(403, 'Unauthorized action.');
        }
        //         dd($request->all());
        $request->validate([
            'sr_no' => 'required',
            'manual_id' => 'required',
            "sop" => 'required',
            'name' => 'required',
            'description' => 'required',
            'model' => 'required',
            'manufacturer' => 'required',
            'supplier' => 'required',
            'lab' => 'required',
            'categories' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $instrument = Instruments::create([
                'business_id' => session('user.business_id'),

                'sr_no' => $request->input('sr_no'),
                'manual_id' => $request->input('manual_id'),
                'sop' => $request->input('sop'),
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'model' => $request->input('model'),
                'manufacturer' => $request->input('manufacturer'),
                'supplier' => $request->input('supplier'),
                'lab' => $request->input('lab'),
                'category' => $request->categories,
            ]);
            AuditLog::create([
                'event' => 'created',
                'module' => 'Equipment',
                'details' => 'Equipment ID: ' . $instrument->id,
                'user_id' => Auth::id(),
            ]);
            DB::commit();

            return back()->with('status', [
                'success' => 1,
                'msg' => __('method.device_added'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return back()->with('status', [
                'success' => 0,
                'msg' => __('Something went wrong'),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Instruments  $instruments
     * @return \Illuminate\Http\Response
     */
    public function show(Instruments $instruments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Instruments  $instruments
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('Devices.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $lab = Role::whereIn('name', [
            'Chemical Lab Manager#15',
            'Physical Lab Manager#15',
            'Micro Lab Manager#15'
        ])->pluck('name', 'name');
        $lab = $lab->map(function ($name) use ($business_id) {
            return str_replace('#' . $business_id, '', $name);
        });



        $device = Instruments::where('business_id', Auth::user()->business_id)->where('id', '=', $id)->first();
        return view('instrument.edit', get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Instruments  $instruments
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('Devices.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'sr_no' => 'required',
            'manual_id' => 'required',
            "sop" => 'required',
            'name' => 'required',
            'description' => 'required',
            'model' => 'required',
            'manufacturer' => 'required',
            'supplier' => 'required',
            'lab' => 'required',
            'categories' => 'required',
        ]);

        try {
            DB::beginTransaction();

            // Fetch the original record
            $original = Instruments::where('id', $id)
                ->where('business_id', session('user.business_id'))
                ->firstOrFail();
            $originaldata = $original->getOriginal();

            // Prepare the update data
            $updateData = [
                'sr_no' => $request->input('sr_no'),
                'manual_id' => $request->input('manual_id'),
                'sop' => $request->input('sop'),
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'model' => $request->input('model'),
                'manufacturer' => $request->input('manufacturer'),
                'supplier' => $request->input('supplier'),
                'lab' => $request->input('lab'),
                'category' => $request->categories,
            ];

            // Update the record
            $updated = $original->update($updateData);

            // Log update event with detailed changes
            $changes = [];
            foreach ($request->all() as $key => $value) {
                if (isset($originaldata[$key]) && $originaldata[$key] != $value) {
                    $changes[] = "$key: '{$originaldata[$key]}' to '$value'";
                }
            }
            $changesDetails = implode(", ", $changes);


            if (!empty($changes)) {
                $changeLog = implode(', ', array_map(function ($key, $change) {
                    return "{$change}";
                }, array_keys($changes), $changes));

                AuditLog::create([
                    'event' => 'updated',
                    'module' => 'Equipment',
                    'details' => "Equipment ID: {$id} changes: {$changeLog}",
                    'user_id' => Auth::id(),
                    'timelog' => now(),
                ]);
            }

            DB::commit();

            return back()->with('status', [
                'success' => 1,
                'msg' => __('method.device_updated'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }





    public function destroy($id)
    {
        if (!auth()->user()->can('Devices.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $device = Instruments::findOrFail($id);
        $device->delete();
        // Log deletion event
        AuditLog::create([
            'event' => 'deleted',
            'module' => 'Equipment',
            'details' => 'Equipment ID: ' . $id,
            'user_id' => Auth::id(),
        ]);
        $devices = Instruments::where('business_id', Auth::user()->business_id)->get();
        return back()->with('status', [
            'success' => 1,
            'msg' => __('method.device_deleted'),
        ]);
    }


    public function callibration(Request $request)
    {
        if (!auth()->user()->can('Devices.callibration_log') && !auth()->user()->can('Devices.callibration.edit') && !auth()->user()->can('Devices.callibration.add') && !auth()->user()->can('Devices.callibration.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $devices = Instruments::where('business_id', Auth::user()->business_id)->get();
        $calibratorDetails = CalibrationDetail::all();
        $business_id = request()->session()->get('user.business_id');


        return view('instrument.callibration', get_defined_vars());
    }


    public function showDeviceDetails(Request $request, $id)
    {

        $selectedDeviceName = $id;

        $selectedDevice = Instruments::where('id', $selectedDeviceName)
            ->where('business_id', Auth::user()->business_id)
            ->first();

        $devices = Instruments::where('business_id', Auth::user()->business_id)->get();
        $calibratorDetails = CalibrationDetail::all();
        $lastCalibration = CalibrationDetail::latest('created_at')->first();

        return view('instrument.callibration', get_defined_vars());
    }

    public function storeCalibration(Request $request)
    {
        if (!auth()->user()->can('Devices.callibration.add')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'device_id' => 'required|exists:instruments,id',
                'calibrator_name' => 'required|string',
                'calibrator_cnic' => 'required|string',
                'calibrator_mobile' => 'required|string',
                'calibration_type' => 'nullable|in:annual,non-annual',
                'calibration_date' => 'nullable|date',
                'guaranteed_date' => 'nullable|date',
                'remarks' => 'nullable|string',
                'calibration_frequency' => 'nullable|in:1,3,6,12',
                'is_repaired' => 'required|boolean',
            ]);

            $user = Auth::user();
            $businessId = $user->business_id;

            // Add 'business_id' to the data array
            $data = $request->all();
            $data['business_id'] = $businessId;

            // Create calibration detail
            $calibration  = CalibrationDetail::create($data);

            // Log creation event
            AuditLog::create([
                'event' => 'created',
                'module' => 'Calibration',
                'details' => 'Calibration ID: ' . $calibration->id,
                'user_id' => Auth::id(),
            ]);

            // Retrieve devices and calibrator details
            $devices = Instruments::where('business_id', Auth::user()->business_id)->get();
            $calibratorDetails = CalibrationDetail::all();

            return back()->with([
                'status' => ['success' => 1, 'msg' => __('method.calibration_created')],
                'devices' => $devices,
                'calibratorDetails' => $calibratorDetails,
            ]);
        } catch (\Exception $e) {


            return back()->with([
                'status' => ['success' => 0, 'msg' => __('messages.something_went_wrong')],
            ]);
        }
    }



    public function destroyCalibrator($id)
    {
        if (!auth()->user()->can('Devices.callibration.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $calibrator = CalibrationDetail::findOrFail($id);
        $calibrator->delete();
        AuditLog::create([
            'event' => 'deleted',
            'module' => 'Calibration',
            'details' => 'Calibration ID: ' . $id,
            'user_id' => Auth::id(),
        ]);
        $calibratorDetails = CalibrationDetail::all();
        $devices = Instruments::where('business_id', Auth::user()->business_id)->get();

        return view('instrument.callibration')
            ->with('success', 'Calibrator has been deleted successfully')
            ->with('calibratorDetails', $calibratorDetails)
            ->with('devices', $devices);
    }


    public function updateCalibrator(Request $request, $id)
    {
        if (!auth()->user()->can('Devices.callibration.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $calibrator = CalibrationDetail::findOrFail($id);
        $request->validate([
            // 'device_id' => 'required|exists:instruments,id',
            'calibrator_name' => 'required|string',
            'calibrator_cnic' => 'required|string',
            'calibrator_mobile' => 'required|string',
            'calibration_type' => 'nullable|in:annual,non-annual',
            'calibration_date' => 'nullable|date',
            'guaranteed_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'calibration_frequency' => 'nullable|in:1,3,6,12',
            'is_repaired' => 'required|boolean',

        ]);


        $calibrator->update([
            'calibrator_name' => $request->input('calibrator_name'),
            'calibrator_cnic' =>  $request->input('calibrator_cnic'),
            'calibrator_mobile' => $request->input('calibrator_mobile'),
            'calibration_type' =>  $request->input('calibration_type'),
            'calibration_date' => $request->input('calibration_date'),
            'guaranteed_date' =>  $request->input('guaranteed_date'),
            'remarks' =>  $request->input('remarks'),
            'calibration_frequency' =>  $request->input('calibration_frequency'),
            'is_repaired' =>  $request->input('is_repaired'),
        ]);
        AuditLog::create([
            'event' => 'updated',
            'module' => 'Calibration',
            'details' => 'Calibration ID: ' . $id,
            'user_id' => Auth::id(),
        ]);
        $calibratorDetails = CalibrationDetail::all();
        $devices = Instruments::where('business_id', Auth::user()->business_id)->get();

        return view('instrument.callibration')
            ->with('success', 'Calibrator has been updated successfully')
            ->with('calibratorDetails', $calibratorDetails)
            ->with('devices', $devices);
    }
    public function showCalibratorDetails($id)
    {
        $calibrator = CalibrationDetail::findOrFail($id);
        return view('instrument.showDetails', compact('calibrator'));
    }


    public function showDeviceView(Request $request, $id, $module = null)
    {
        $business_id = request()->session()->get('user.business_id');
        // Define the possible module names
        $modules = ['Utilization', 'Capa', 'Equipment'];

        // Retrieve data for the specified equipment and related models
        $equipment = Instruments::findOrFail($id);
        $capa = Capa::where("device_id", $id)->get();
        $utilizations = Utilization::where("device_id", $id)->get();

        $devices = Instruments::where('business_id', $business_id)->get();
        $samples = Product::where('business_id', $business_id)->where('product_type', 'sample')->get()->unique('name');

        // Determine the tab view, defaulting to 'information'
        if (is_null(request()->get('view'))) {
            $tab_view = 'information';
        } else {
            $tab_view = request()->get('view');
        }
        // Handle AJAX request
        if ($request->ajax()) {
            $module = $request->input('module');

            // Set modules to default if no module is provided
            if (is_null($module)) {
                $module = $modules;
            } else {
                $module = (array) $module;
            }

            // Retrieve logs based on the provided or default modules
            $logs = AuditLog::whereIn('module', $module)->orderBy('created_at', 'desc')->get();

            $view = view('instrument.viewtabs.logs_table', compact('logs'))->render();

            return response()->json(['html' => $view]);
        }

        $total_capa = $capa->count('id');
        $progress_capa = $capa->whereIn('status', ['In Progress', 'pending'])->count('id');
        $completed_capa = $capa->where('status', 'completed')->count('id');



        $monthname = [];
        $data = [];

        $currentYear = Carbon::now()->year;
        // Add the month name to the array


        // Iterate through the months of the year
        for ($month = 1; $month <= 12; $month++) {

            $carbonDate = Carbon::create($currentYear, $month, 29)->format('F');

            $monthname[] = $carbonDate;
            $startOfMonth = Carbon::create($currentYear, $month, 29)->startOfMonth();
            $endOfMonth = Carbon::create($currentYear, $month, 29)->endOfMonth();

            $monthData = $utilizations->where('created_at', '>=', $startOfMonth)
                ->where('created_at', '<=', $endOfMonth)
                ->count();

            // If data is present for the current month, add it to the data array
            if ($monthData) {
                $data[] = $monthData;
            } else {
                // If no data is present, add a dummy value
                $data[] = 0;
            }
        }

        $Calibration = CalibrationDetail::where('device_id', $id)->get();
        $lastCalibration = CalibrationDetail::where('device_id', $id)->latest('created_at')->first();

        $deviations = Deviation::with('device', 'sample', 'batch', 'test')->where('device_id', $id)->get();



        return view('instrument.view', get_defined_vars());
    }







    public function showInformation($id)
    {
        $equipment = Instruments::findOrFail($id);
        return view('instrument.viewtabs.information', compact('equipment', 'id'));
    }

    public function showCapa($id)
    {
        $capa = Capa::where('device_id', $id)->get();

        $total_capa = $capa->count('id');
        $progress_capa = $capa->whereIn('status', ['In Progress', 'pending'])->count('id');
        $completed_capa = $capa->where('status', 'completed')->count('id');
        return view('instrument.viewtabs.capa', compact('capa', 'id', 'total_capa', 'progress_capa', 'completed_capa'));
    }

    public function showUtilization($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $utilizations = Utilization::where('device_id', $id)->get();
        $devices = Instruments::where('business_id', $business_id)->get();
        $samples = Product::where('business_id', $business_id)->where('product_type', 'sample')->get()->unique('name');
        return view('instrument.viewtabs.utilization', compact('utilizations', 'id', 'devices', 'samples'));
    }

    public function showCalibration($id)
    {
        $calibration = CalibrationDetail::where('device_id', $id)->get();
        return view('instrument.viewtabs.calibration', compact('calibration', 'id'));
    }

    public function showDeviation($id)
    {
        $deviations = Deviation::where('device_id', $id)->get();
        return view('instrument.viewtabs.deviation', compact('deviations', 'id'));
    }

    public function showLogs($id)
    {
        $logs = AuditLog::where('module', 'Equipment')
            ->where('details', 'LIKE', '%Equipment ID: ' . $id . '%')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('instrument.viewtabs.logs', compact('logs', 'id'));
    }










    public function showLabel($id)
    {
        //        dd($id);
        // Fetch device data from the database
        $device = Instruments::findOrFail($id); // Replace Device with your actual model


        // Generate the barcode or QR code
        $qrCodeText = url("/scan/equipment/" . $device->id . "/tests") . "\n" . $device->name . "\n" . $device->model;
        //        dd($qrCodeText);
        //        $barcodeGenerator = new BarcodeGeneratorHTML();
        //        $barcode = $barcodeGenerator->getBarcode($device->id, BarcodeGeneratorHTML::TYPE_CODE_128);
        //dd(55555);
        // Return view with label data
        return view('instrument.viewtabs.printlable', compact('device', 'qrCodeText'));
    }
    public function scanview(Request $request, $id, $module = null)
    {
        //dd($request, $id, $module = null);
        // Define the possible module names
        $modules = ['Utilization', 'Capa', 'Equipment'];

        // Retrieve data for the specified equipment and related models
        $equipment = Instruments::findOrFail($id);
        $capa = Capa::where("device_id", $id)->get();
        $utilizations = Utilization::with('chemical.product', 'standard.product')->where("device_id", $id)->get();

        //        $devices = Instruments::where('business_id', Auth::user()->business_id)->get();
        //        $samples = Product::where('business_id', Auth::user()->business_id)->get();

        // Determine the tab view, defaulting to 'information'
        if (is_null(request()->get('view'))) {

            $tab_view = 'information';
        } else {

            $tab_view = request()->get('view');
        }

        // Handle AJAX request
        if ($request->ajax()) {

            $module = $request->input('module');

            // Set modules to default if no module is provided
            if (is_null($module)) {

                $module = $modules;
            } else {

                $module = (array) $module;
            }

            // Retrieve logs based on the provided or default modules
            $logs = AuditLog::whereIn('module', $module)->orderBy('created_at', 'desc')->get();

            $view = view('instrument.viewtabs.logs_table', compact('logs'))->render();

            return response()->json(['html' => $view]);
        }

        $total_capa = $capa->count('id');
        $progress_capa = $capa->whereIn('status', ['In Progress', 'pending'])->count('id');
        $completed_capa = $capa->where('status', 'completed')->count('id');



        $monthname = [];
        $data = [];

        $currentYear = Carbon::now()->year;
        // Add the month name to the array


        // Iterate through the months of the year
        for ($month = 1; $month <= 12; $month++) {

            $carbonDate = Carbon::create($currentYear, $month, 29)->format('F');

            $monthname[] = $carbonDate;
            $startOfMonth = Carbon::create($currentYear, $month, 29)->startOfMonth();
            $endOfMonth = Carbon::create($currentYear, $month, 29)->endOfMonth();

            $monthData = $utilizations->where('created_at', '>=', $startOfMonth)
                ->where('created_at', '<=', $endOfMonth)
                ->count();
            // If data is present for the current month, add it to the data array
            if ($monthData) {
                $data[] = $monthData;
            } else {
                // If no data is present, add a dummy value
                $data[] = 0;
            }
        }

        $Calibration = CalibrationDetail::where('device_id', $id)->get();
        $lastCalibration = CalibrationDetail::where('device_id', $id)->latest('created_at')->first();

        $deviations = Deviation::with('device', 'sample', 'batch', 'test')->where('device_id', $id)->get();



        return view('instrument.scanview', get_defined_vars());
    }
}
