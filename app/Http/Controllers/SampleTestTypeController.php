<?php

namespace App\Http\Controllers;

use App\SampleTestType;
use Illuminate\Http\Request;

class SampleTestTypeController extends Controller
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        SampleTestType::create([
            "type_name" => $request->type_name,
            'short_desc' => $request->short_desc,
            'sample_id' => $request->sample_id,
            'business_id' => $business_id,
        ]);
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SampleTestType  $sampleTestType
     * @return \Illuminate\Http\Response
     */
    public function show(SampleTestType $sampleTestType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SampleTestType  $sampleTestType
     * @return \Illuminate\Http\Response
     */
    public function edit(SampleTestType $sampleTestType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SampleTestType  $sampleTestType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SampleTestType $sampleTestType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SampleTestType  $sampleTestType
     * @return \Illuminate\Http\Response
     */
    public function destroy(SampleTestType $sampleTestType)
    {
        //
    }
}
