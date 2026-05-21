<?php

namespace App\Http\Controllers;

use App\Product;
use App\Formulas;
use Carbon\Carbon;
use App\SampleReading;
use App\CustomFieldGroup;
use Illuminate\Http\Request;
use App\CustomFieldGroupLable;
use Illuminate\Support\Facades\DB;

class SampleReadingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('Sample Tests.Reading_and_test')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $products = Product::where('business_id', $business_id)->where('product_type', 'sample') ->groupBy('name')->pluck('name', 'id')->all();
        $products = ['' => 'Select a Sample'] + $products;
        // $groups = CustomFieldGroup::where('business_id', $business_id)->pluck('name', 'id')->all();
        $formula = Formulas::where('business_id', $business_id)->pluck('formula', 'id')->all();
        return view('sample_reading.create')->with(compact('products', 'formula'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->authorize('Sample Tests.Reading_and_test'); // Authorization check
            $request->validate([
                'groups' => 'required|array',
            ]);

            DB::beginTransaction();

            foreach ($request->input('groups') as $group) {
                $result = [];

                for ($j = 0; $j < count($request->input('label_names' . $group)); $j++) {
                    $result[$request->input('label_names' . $group)[$j]] = $request->input('label_values' . $group)[$j];
                }

                $json = json_encode($result);

                SampleReading::where('business_id', auth()->user()->business_id)->where('test', $request->test_id)->where('group_id', $group)
                    ->update([
                        'group_reading' => $json,
                        'value' => $request->input('val' . $group),
                    ]);
            }

            DB::commit();

            return back()->with('status', [
                'success' => 1,
                'msg' => __('method.reading_added_success'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SampleReading  $sampleReading
     * @return \Illuminate\Http\Response
     */
    public function show(SampleReading $sampleReading)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SampleReading  $sampleReading
     * @return \Illuminate\Http\Response
     */
    public function edit(SampleReading $sampleReading)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SampleReading  $sampleReading
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SampleReading $sampleReading)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SampleReading  $sampleReading
     * @return \Illuminate\Http\Response
     */
    public function destroy(SampleReading $sampleReading)
    {
        //
    }

    public function groupdata(Request $request)
    {
        $method = SampleReading::with('groups', 'groups.lables')->where('business_id', auth()->user()->business_id)->where('test', $request->samplegroup)->get();
        // $business_id = request()->session()->get('user.business_id');
        // dd($request->all(),$method);
        // $formula = Formulas::where('business_id', $business_id)->where('id', $request->id)->first();
        // $formula = $formula->formula;
        // $extractedSubstrings = [];
        // $pattern = '/[A-Z]+/';
        // if (preg_match_all($pattern, $formula, $matches)) {
        //     $extractedSubstrings = $matches[0];
        // }

        // $uniqueSubstrings = array_unique($extractedSubstrings);

        // $groups = CustomFieldGroup::with('lables')->whereIn('name', $uniqueSubstrings)->get();
        return view('samplegroup.readings')->with(compact('method'));
    }

    public function detail_report(Request $request)
    {
        if (!auth()->user()->can('detail_report.view') ) {
            abort(403, 'Unauthorized action.');
        }


        return view('sample_reading.detail_report');
    }
}
