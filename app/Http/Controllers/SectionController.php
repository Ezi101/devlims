<?php

namespace App\Http\Controllers;

use App\Section;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;


class SectionController extends Controller
{


    public function index()
    {
        if (!auth()->user()->can('section.view') ) {
            abort(403, 'Unauthorized action.');
        }
        $section = Section::where('business_id', auth()->user()->business_id)->get();
        return  view('section.index')->with(compact('section'));
    }

    public function create(Request $request)
    {
        if (!auth()->user()->can('section.create')) {
            abort(403, 'Unauthorized action.');
        }
        // dd($request->all());
        return view('section.create');
    }


    public function store(Request $request)
    {
        $this->authorize('section.create'); // Authorization check

        $request->validate([
            'name' => 'required',
            'description' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $section = Section::create([
                'business_id' => session('user.business_id'),
                'code' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            DB::commit();

            return back()->with('status', [
                'success' => 1,
                'msg' => __('product.product_added_success'),
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



    public function edit()
    {
    }


    public function update()
    {
    }


    public function destroy($section)
    {
        if (!auth()->user()->can('section.delete')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $output = '';
            try {

                // if (!empty($product)) {
                DB::beginTransaction();
                $business_id = request()->session()->get('user.business_id');
                //Delete variation location details
                Section::where('business_id', $business_id)->where('id', $section)->delete();

                DB::commit();
                $output = [
                    'success' => true,
                    'msg' => __('product.section_delete_success'),
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
