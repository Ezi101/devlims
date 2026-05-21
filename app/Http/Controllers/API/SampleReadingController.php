<?php

namespace App\Http\Controllers\API;

use App\Product;
use App\Formulas;
use Carbon\Carbon;
use App\SampleReading;
use App\CustomFieldGroup;
use Illuminate\Http\Request;
use App\CustomFieldGroupLable;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller as Controller;

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
        if (!auth()->user()->can('product.view') && !auth()->user()->can('product.create')) {
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
    public function reading($id, $group)
    {

        $data = $_GET;

        try {
            // DB::beginTransaction();
            if (is_null($id)) {
                return response()->json([
                    'success' => 'false',
                    'data' => "test id required",
                ], 200);
            }

            $method = SampleReading::where('test', $id)->first();
            if ($method) {
                $group = CustomFieldGroup::where('name', $group)->first();
                if ($group) {
                    $method = SampleReading::where('test', $id)->where('group_id', $group->id)->first();
                    $lables = CustomFieldGroupLable::where('group_id', $group->id)->get();
                    $result = [];
                    if ($data) {
                        $array = explode(",", $data['values']);

                        for ($j = 0; $j < count($lables); $j++) {
                            $result[$lables[$j]->lable] = isset($array[$j]) ? $array[$j] : 0;
                        }
                        $json = json_encode($result);
                        $section = SampleReading::where('test', $id)->where('group_id', $group->id)->update([
                            'group_reading' => $json,
                        ]);
                        // dd($section,$json);
                        return response()->json([
                            'success' => 'true',
                            'data' => "Data updated",
                        ], 200);
                    }
                    return response()->json([
                        'success' => 'false',
                        'data' => "No Data Update",
                    ], 200);
                } else {
                    return response()->json([
                        'success' => 'false',
                        'data' => "No test Found",
                    ], 200);
                }
            }

            // DB::commit();

            return back()->with('status', [
                'success' => 1,
                'msg' => __('method.reading_added_success'),
            ]);
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }


    public function readings2(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'pdf' => 'nullable|file|mimes:pdf',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'data' => $validator->errors()->first('pdf'),
            ], 400);
        }

        // Check if test_id is present
        if (is_null($request->test_id)) {
            return response()->json([
                'success' => 'false',
                'data' => "Test Id required",
            ], 400);
        }

        // Check if group is present
        if (is_null($request->group)) {
            return response()->json([
                'success' => 'false',
                'data' => "Group name required",
            ], 400);
        }

        $pdfPath = null; // Initialize the PDF path variable

        // Fetch the method based on the test_id
        $method = SampleReading::where('test', $request->test_id)->first();

        // Ensure the method's status is not 'completed'
        if ($method && $method->status != 'completed') {
            // Fetch the group based on the group name
            $group = CustomFieldGroup::where('id', 'LIKE', '%' . $request->group . '%')->first();

            if ($group) {
                // Check if the group's ID matches
                if ($method->group_id != $group->id) {
                    return response()->json([
                        'success' => 'false',
                        'data' => "Group not matched!",
                    ], 400);
                }

                // Handle PDF upload if present
                if ($request->hasFile('pdf')) {
                    $file = $request->file('pdf');
                    $thumbnailimage = time() . '.' . $file->extension();
                    $file->move(public_path('test_pdf'), $thumbnailimage);
                    $pdfPath = 'test_pdf/' . $thumbnailimage; // Store the PDF file path
                }

                // Update the method with the group ID and PDF path
                $section = $method->update([
                    'group_reading' => null, // Removed the result array logic
                    // 'status' => 'completed',
                    'pdf' => $pdfPath,
                ]);

                return response()->json([
                    'success' => 'true',
                    'data' => "Data updated successfully!",
                ], 200);
            } else {
                return response()->json([
                    'success' => 'false',
                    'data' => "Group Not Found!",
                ], 400);
            }
        } else {
            return response()->json([
                'success' => 'false',
                'data' => "Test Completed Data not update!",
            ], 400);
        }
    }


    public function readings(Request $request)
    {
        // dd($request->all());

        // Validation rules
        $validator = Validator::make($request->all(), [
            'pdf' => 'nullable|file|mimes:pdf',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'data' => $validator->errors()->first('pdf'),
            ], 400);
        }

        try {
            // DB::beginTransaction();
            if (is_null($request->test_id)) {
                return response()->json([
                    'success' => 'false',
                    'data' => "Test_id required",
                ], 200);
            }

            if (is_null($request->values)) {
                return response()->json([
                    'success' => 'false',
                    'data' => "Values required",
                ], 200);
            }
            if (is_null($request->group)) {
                return response()->json([
                    'success' => 'false',
                    'data' => "Group Name required",
                ], 200);
            }

            if (count($request->values) != count($request->lables)) {
                return response()->json([
                    'success' => 'false',
                    'data' => "Length must be Same",
                ], 200);
            }

            $pdfPath = null; // Initialize the PDF path variable

            if ($request->hasFile('pdf')) {
                $file = $request->file('pdf');
                $thumbnailimage = time() . '.' . $file->extension();
                $file->move(public_path('test_pdf'), $thumbnailimage);
                $pdfPath = 'test_pdf/' . $thumbnailimage; // Store the PDF file path
            }

            // dd($thumbnailimage);

            $method = SampleReading::where('test', $request->test_id)->first();
            if ($method) {
                $group = CustomFieldGroup::where('name', $request->group)->first();
                if ($group)
                    $method = SampleReading::where('test', $request->test_id)->where('group_id', $group->id)->first();
                $lables = CustomFieldGroupLable::where('group_id', $group->id)->get();

                // if ($request->values) {
                //     $labelsWithValues = []; // Array to hold labels with their corresponding values
                //     foreach ($request->lables as $index => $label) {
                //         $labelsWithValues[$label . ' label'] = $request->values[$index];
                //     }

                //     // Use the labels with values to update the corresponding entries in the database
                //     $d = json_decode($method->group_reading, true);
                //     $index = array_keys($d);
                //     $values = array_values($d);

                //     foreach ($index as $i => $key) {
                //         if (isset($labelsWithValues[$key])) {
                //             $values[$i] = $labelsWithValues[$key];
                //         }
                //     }

                //     $newArray = array_combine($index, $values);
                //     $json = json_encode($newArray);
                //     $section = SampleReading::where('test', $request->test_id)
                //         ->where('group_id', $group->id)
                //         ->update([
                //             'group_reading' => $json,
                //         ]);
                //         dd($d,$index,$values,$newArray,$json);                    
                // }

                // old code
                $result = [];
                if ($request->values) {
                    // $array = explode(",", $data['values']);

                    for ($j = 0; $j < count($lables); $j++) {
                        $result[$lables[$j]->lable] = isset($request->values[$j]) ? $request->values[$j] : 0;
                    }
                    $json = json_encode($result);

                    $section = SampleReading::where('test', $request->test_id)->update([
                        'group_reading' => "23",
                    ]);
                    return response()->json([
                        'success' => 'true',
                        'data' => "Data updated",
                    ], 200);
                } else {
                    return response()->json([
                        'success' => 'false',
                        'data' => "No Data Update",
                    ], 200);
                }

                // DB::commit();
                // return response()->json([
                //     'success' => 'true',
                //     'data' => "Data Updated",
                // ], 200);
            }
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return response()->json([
                'success' => 'true',
                'data' =>  __('messages.something_went_wrong'),
            ], 200);
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
        $business_id = request()->session()->get('user.business_id');
        $formula = Formulas::where('business_id', $business_id)->where('id', $request->id)->first();
        $formula = $formula->formula;
        $extractedSubstrings = [];
        $pattern = '/[A-Z]+/';
        if (preg_match_all($pattern, $formula, $matches)) {
            $extractedSubstrings = $matches[0];
        }

        $uniqueSubstrings = array_unique($extractedSubstrings);

        $groups = CustomFieldGroup::with('lables')->whereIn('name', $uniqueSubstrings)->get();
        return view('sample_reading.lables')->with(compact('groups'));
    }

    public function detail_report(Request $request)
    {
        return view('sample_reading.detail_report');
    }
}
