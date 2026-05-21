<?php

namespace App\Http\Controllers;

use App\TestGroup;
use Illuminate\Http\Request;
use DB;

class TestGroupController extends Controller
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
    public function create(Request $request)
    {
        if (!auth()->user()->can('Sample Tests.associated_test.create')) {
            abort(403, 'Unauthorized action.');
        }

        $quick_add = false;
        if (!empty(request()->input('quick_add'))) {
            $quick_add = true;
        }

        return view('test_group.create')->with(compact('quick_add'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeTest(Request $request)
    {
        if (!auth()->user()->can('Sample Tests.associated_test.create')) {
            abort(403, 'Unauthorized action.');
        }
        $this->validate($request,[
            'name' => 'required',
            'description' => 'required',
        ]);

        DB::beginTransaction();
        try {

            $business_id = $request->session()->get('user.business_id');
            $test = TestGroup::create([

                'business_id' => $business_id,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'test' => $test]);
        } catch (\Exception $e) {

            DB::rollback();

            return response()->json(['success' => false]);
        }

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (!auth()->user()->can('Sample Tests.associated_test.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'description']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            $batch = TestGroup::create($input);
            $output = [
                'success' => true,
                'data' => $batch,
                'msg' => __('method.test_group_added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
        //
    }
    public function test_list(Request $request)
    {
        if (!auth()->user()->can('Sample Tests.associated_test.view')) {
            abort(403, 'Unauthorized action.');
        }


        $business_id = $request->session()->get('user.business_id');
        $testLists = TestGroup::where('business_id', $business_id)->orderBy('id','desc')->get();

        return view('test_group.testList')->with(compact('testLists'));
    }

    public function editTest(Request $request){

        
        $testId = $request->input('id');
        $business_id = $request->session()->get('user.business_id');
        
        $testList = TestGroup::where('business_id', $business_id)->where('id',$testId)->first();

        return view('test_group.edit_test_model',compact('testList'));
    }
    public function updateAssociatedTest(Request $request)
    {
        $testData = TestGroup::where('id', $request->test_id)->first();
        if ($testData) {
            $testData->name = $request->name;
            $testData->description = $request->description;
            $testData->update();
            return redirect()->back()->with('status', ['success' => 1, 'msg' => __('Test Update')]);
        } else {
            return redirect()->back()->with('status', ['error' => 1, 'msg' => __('Test not found')]);
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
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
