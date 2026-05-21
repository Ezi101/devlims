<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\GenericName;

class GenericNameController extends Controller
{
    public function index(Request $request)
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $quick_add = false;
        if (!empty(request()->input('quick_add'))) {
            $quick_add = true;
        }

        return view('generic name.create')->with(compact('quick_add'));
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
        if (!auth()->user()->can('brand.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'description']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            $generic_name = GenericName::create($input);

            return response()->json([
                'success' => 1,
                'msg' => __('Generic Added'),
                'generic_name' => [
                    'id' => $generic_name->id,
                    'name' => $generic_name->name,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return redirect()->back()->with('error', __('messages.something_went_wrong'));
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
    public function edit($batches)
    {
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
