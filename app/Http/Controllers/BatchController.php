<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Product;
use App\Transaction;
use App\PurchaseLine;
use App\STR;
use App\SampleReading;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Modules\Project\Entities\Project;
use Modules\Project\Entities\ProjectTask;
use DataTables;

class BatchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $businessId = auth()->user()->business_id;

        $sample = Product::where('business_id', $businessId)->groupBy('name')->get();

        return view('batch.index', compact('sample'));
    }

    /**
     * Load Price Policy Index Table
     */
    public function loadTable(Request $request)
    {
        $data = PurchaseLine::with('sample', 'batch')->when($request->sample != '', function ($q) use ($request) {
            $q->where('product_id', '=', $request->sample);
        })->when($request->from_date, function ($q) use ($request) {
            $q->where('created_at', '>=', $request->from_date);
        })->when($request->to_date, function ($q) use ($request) {
            $q->where('created_at', '<=', $request->to_date);
        })->get();

        return Datatables::of($data)
            ->addColumn('batch', function ($data) {
                $batch = '';
                if ($data->batch) {
                    return $data->batch->code;
                } else {
                    return '--';
                }
            })
            ->addColumn('sample', function ($data) {
                $sample = '';
                if ($data->sample) {
                    return $data->sample->name;
                } else {
                    return '--';
                }
            })
            ->addColumn('created_date', function ($data) {
                return $data->created_at->format('d-m-Y');
            })
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // dd($request->all());
        $quick_add = false;
        if (!empty(request()->input('quick_add'))) {
            $quick_add = true;
        }
        $product_id = $request->product_id;
        // dd($product_id);
        return view('batch.create')->with(compact('quick_add', 'product_id'));
    }
    public function createLotNo(Request $request)
    {
        // dd($request->all());
        $quick_add = false;
        if (!empty(request()->input('quick_add'))) {
            $quick_add = true;
        }
        $product_id = $request->product_id;
        // dd($product_id);
        return view('reagent.partials.lot_no_model')->with(compact('quick_add', 'product_id'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // if (!auth()->user()->can('brand.create')) {
        //     abort(403, 'Unauthorized action.');
        // }

        try {
            $business_id = $request->session()->get('user.business_id');
            $input = $request->only(['code', 'description', 'mfg_date', 'expiry_date',]);
            $input['business_id'] = $business_id;
            $input['sample_id'] = $request->sample_id;

            $batch = Batch::create($input);

            AuditLogger::log('created', 'Batch', 'Batch ID: ' . $batch->id . '& Batch Code: ' . $batch->code);

            return redirect()->back()->with('status', ['success' => true, 'msg' => __('batch.added_success')]);
        } catch (\Exception $e) {
            return redirect()->back()->with('status', ['success' => false, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function get_test_list(Request $request, $id)
    {

        $business_id = $request->session()->get('user.business_id');

        $product = Batch::where('business_id', $business_id)->where('id', $id)->first();
        $project = Project::where('business_id', $business_id)->where('product_id', $product->sample_id)->first();

        $issue_id = Transaction::where('business_id', $business_id)->where('type', 'sell')->where('batch_no', $id)->pluck('invoice_no');
        $tasks = ProjectTask::with('samplereading')->where('business_id', $business_id)->where('test_on_issue_id', $issue_id)->get();
        // dd($issue_id,$tasks);

        return view('batch.list_test', compact('tasks', 'product'));
    }

    public function get_str_list(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        $strs = Str::with('batch', 'contract', 'contact', 'product')->where('business_id', $business_id)->where('batch_no', $id)->groupby('str_no')->get();
        $product = Batch::where('business_id', $business_id)->where('id', $id)->first();

        return view('batch.list_strs', compact('strs', 'product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($batches)
    {
        // dd($batches);
        $quick_add = false;
        if (!empty(request()->input('quick_add'))) {
            $quick_add = true;
        }

        $batches = Batch::where('id', $batches)->first();

        return view('batch.edit')->with(compact('quick_add', 'batches'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('brand.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Find the batch by ID
            $batch = Batch::findOrFail($id);

            // Capture the old values
            $oldValues = $batch->only(['code', 'mfg_date', 'expiry_date', 'description']);

            // Update the batch data
            $input = $request->only(['code', 'mfg_date', 'expiry_date', 'description']);
            $batch->update($input);

            // Capture the new values
            $newValues = $batch->only(['code', 'mfg_date', 'expiry_date', 'description']);

            // Prepare a mapping for user-friendly field names
            $fieldNames = [
                'code' => 'Batch Code',
                'mfg_date' => 'Manufacturing Date',
                'expiry_date' => 'Expiry Date',
                'description' => 'Description'
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
            $logMessage = "<b>Batch ID: " . $batch->id . "</b> was <b>updated:</b><br>" . $changesDetails;
            AuditLogger::log('updated', 'Batch', $logMessage);

            return redirect()->back()->with('status', ['success' => true, 'msg' => __('batch.updated_success')]);
        } catch (\Exception $e) {
            return redirect()->back()->with('status', ['success' => false, 'msg' => __('messages.something_went_wrong')]);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
