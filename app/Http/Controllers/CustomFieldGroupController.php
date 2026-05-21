<?php

namespace App\Http\Controllers;

use App\CustomFieldGroup;
use App\CustomFieldGroupLable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomFieldGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('Sample Tests.list_group')) {
            abort(403, 'Unauthorized action.');
        }
        $group = CustomFieldGroup::with('lables')->where('business_id', auth()->user()->business_id)->get();
        return  view('custom_group.index')->with(compact('group'));  
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('Sample Tests.list_group_add')) {
            abort(403, 'Unauthorized action.');
        }
        $CustomField_Group =CustomFieldGroup::find(1);

        return view('custom_group.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('Sample Tests.list_group_add'); // Authorization check
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            // 'customfield_lable' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $section = CustomFieldGroup::create([
                'business_id' => session('user.business_id'),
                'name' => $request->input('name'),
                'description' => $request->input('description'),  
                'status' => $request->input('result'),  
            ]);
            $groupid = $section->id;
            if($request->input('customfield_lable')){
                foreach ($request->input('customfield_lable') as $key=>$value){
                    $section = CustomFieldGroupLable::create([
                        'group_id' => $groupid,
                        'lable' => $value,
                        'short_code' => $request->value_lable[$key],
                    ]);
                }
            }

            DB::commit();

            return back()->with('status', [
                'success' => 1,
                'msg' => __('method.group_added_success'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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
     * @param  \App\CustomFieldGroup  $customFieldGroup
     * @return \Illuminate\Http\Response
     */
    public function show(CustomFieldGroup $customFieldGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CustomFieldGroup  $customFieldGroup
     * @return \Illuminate\Http\Response
     */
    public function edit($customFieldGroup)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $CustomField_Group =CustomFieldGroup::with('lables')->where('business_id', $business_id)->find($customFieldGroup);

        return view('custom_group.edit')->with(compact('CustomField_Group'));
    
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CustomFieldGroup  $customFieldGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $customFieldGroup)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }
        // dd($request->all());

        try {
            $business_id = $request->session()->get('user.business_id');
            DB::beginTransaction();

            CustomFieldGroup::where('id', $customFieldGroup)->update([
                'description' => $request->input('description'),
                'status' => $request->input('result'),
            ]);
            
            // Determine which row you want to update and which ones you want to delete.
            $existingRows = CustomFieldGroupLable::where('group_id', $customFieldGroup)->get();
            $deleteIds = $existingRows->pluck('id')->diff($request->input('customfield_lable_edit_id'));
            
            // Update the specific row(s).
            for ($i=0; $i<count($request->input('customfield_lable_edit_id')); $i++) {
                CustomFieldGroupLable::where('id', $request->input('customfield_lable_edit_id')[$i])->update(['lable' => $request->input('customfield_lable_edit')[$i],'short_code' => $request->value_lable_edit[$i]]);
            }

            // Delete the other row(s).
            CustomFieldGroupLable::whereIn('id', $deleteIds)->delete();
                
            if($request->input('customfield_lable')){
                foreach ($request->input('customfield_lable') as $key=>$value){
                    $section = CustomFieldGroupLable::create([
                        'group_id' => $customFieldGroup,
                        'lable' => $value,
                        'short_code' => $request->value_lable[$key],
                    ]);
                }
            }
            
            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('product.product_updated_success'),
            ];
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect('customfieldgroup')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CustomFieldGroup  $customFieldGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy($customfieldgroup)
    {
        if (!auth()->user()->can('product.delete')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $output = '';
            try {
    
                    // if (!empty($product)) {
                        DB::beginTransaction();
                        $business_id = request()->session()->get('user.business_id');
                        //Delete variation location details
                        CustomFieldGroup::where('business_id', $business_id)->where('id', $customfieldgroup)->delete();
        
                        DB::commit();
                        $output = [
                            'success' => true,
                            'msg' => __('method.group_delete_success'),
                        ];
                    // }

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }
}
