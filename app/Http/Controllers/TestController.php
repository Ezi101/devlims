<?php

namespace App\Http\Controllers;

use App\Method;
use Carbon\Carbon;
use App\SampleReading;
use App\CustomFieldGroup;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('Sample Tests.list_test')) {
            abort(403, 'Unauthorized action.');
        }

        $method = SampleReading::with('samples', 'formulas')
            ->where('business_id', auth()->user()->business_id)->where('formula_id','!=','null')
            ->groupBy('test')
            ->get();

        return  view('method.index')->with(compact('method'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('Sample Tests.list_test')) {
            abort(403, 'Unauthorized action.');
        }
        return view('method.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Method  $method
     * @return \Illuminate\Http\Response
     */
    public function show($test)
    {
        // $method = SampleReading::with('samples', 'formulas', 'groups')->where('business_id', auth()->user()->business_id)->where('test', $test)->groupby('group_id')->get();



        // $formula = $method[0]->formulas->formula;
        // $extractedSubstrings = [];
        // $pattern = '/[A-Z]+/';
        // if (preg_match_all($pattern, $formula, $matches)) {
        //     $extractedSubstrings = $matches[0];
        // }

        // $uniqueSubstrings = array_unique($extractedSubstrings);
        // // dd($uniqueSubstrings);
        // $groups = CustomFieldGroup::whereIn('name', $uniqueSubstrings)->pluck('id');
        // $values = SampleReading::with('groups')->whereIn('group_id', $groups)->where('test', $test)->get();



        $sample_reading_details = SampleReading::with('samples', 'formulas', 'groups','project','task','task.members','samples.sections','task.transaction','task.transaction.batch')->where('business_id', auth()->user()->business_id)->where('test', $test)->groupby('test')->first();

        $method = SampleReading::with('samples', 'formulas', 'groups')->where('business_id', auth()->user()->business_id)->where('test', $test)->groupby('group_id')->get();

        $values = SampleReading::with('groups')->where('test', $test)->get();

        // dd($sample_reading_details , $method , $values, $sample_reading_details->task->transaction->batch->code);
        return  view('samplegroup.view')->with(compact('method', 'values','sample_reading_details'));

        return  view('method.view')->with(compact('method', 'uniqueSubstrings', 'values','groups','formula'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Method  $method
     * @return \Illuminate\Http\Response
     */
    public function edit(Method $method)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Method  $method
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Method $method)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Method  $method
     * @return \Illuminate\Http\Response
     */
    public function destroy(Method $method)
    {
        //
    }
}
