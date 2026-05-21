<?php

namespace App\Http\Controllers;

use Log;
use App\Formulas;
use App\CustomFieldGroup;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FormulasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('formula.view')) {
            abort(403, 'Unauthorized action.');
        }
        $formula = Formulas::where('business_id', Auth::user()->business_id)->get();

        return view('formula.index')->with(compact('formula'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!auth()->user()->can('formula.create')) {
            abort(403, 'Unauthorized action.');
        }
        $group = CustomFieldGroup::where('business_id', auth()->user()->business_id)->get();
        return view('formula.create')->with(compact('group'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('formula.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $exist = Formulas::where('business_id', $business_id)->get();
        $requestedFormulaId = $request->formula_id;  // Note the corrected spelling of 'formula_id'

        foreach ($exist as $formula) {
            if ($formula->formula_id === $requestedFormulaId) {
                return back()->with('status', [
                    'error' => 1,
                    'msg' => __('formula.existing_formula'),
                ]);
            }
        }

        try {
            $input = $request->only(['formula_id', 'description', 'status']);
            $input['business_id'] = $business_id;
            $input['formula_id'] = $request->formula_id;
            $input['formula'] = $request->selectedFormulasInput;

            $formula = Formulas::create($input);
            AuditLogger::log('created', 'Formulas', 'Formula ID: ' . $formula->id);

            return back()->with('status', [
                'success' => 1,
                'msg' => __('formula.added_success'),
            ]);
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
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
    public function edit($formula)
    {
        if (!auth()->user()->can('formula.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $group = CustomFieldGroup::where('business_id', auth()->user()->business_id)->get();
        $formula = Formulas::where('business_id', Auth::user()->business_id)->where('id', $formula)->first();
        return view('formula.edit')->with(compact('group', 'formula'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $formula)
    {
        // Check if the user has permission to update a brand (you can replace 'brand.update' with your specific permission)
        if (!auth()->user()->can('formula.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        // $exist = Formulas::where('business_id', $business_id)->get();
        // $requestedFormulaId = $request->input('formula_id');  // Corrected spelling of 'formula_id'

        // Check if the requested formula_id exists in the database except for the current ID being updated
        // foreach ($exist as $exis) {
        //     if ($exis->formula_id === $requestedFormulaId && $exis->id !== $formula) {
        //         return back()->with('status', [
        //             'error' => 1,
        //             'msg' => __('formula.existing_formula'),
        //         ]);
        //     }
        // }

        try {
            $input = $request->only(['description', 'status', 'selectedFormulasInput']);
            $input['business_id'] = $business_id;

            // Find the formula by its ID and update it with the new data
            $u_formula = Formulas::findOrFail($formula);
            $u_formula->update($input);
            AuditLogger::log('updated', 'Formulas', 'Formula ID: ' . $formula .' was updated');

            return redirect('formulas')->with('status', [
                'success' => 1,
                'msg' => __('Formula was Updated'),
            ]);
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];

            // Handle the exception and return an appropriate response
            return back()->with('status', $output);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($formula)
    {
        if (!auth()->user()->can('formula.delete')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $output = '';
            try {

                // if (!empty($product)) {
                DB::beginTransaction();
                $business_id = request()->session()->get('user.business_id');
                //Delete variation location details
                Formulas::where('business_id', $business_id)->where('id', $formula)->delete();
                AuditLogger::log('deleted', 'Formulas', 'Formula ID: ' . $formula);

                DB::commit();
                $output = [
                    'success' => true,
                    'msg' => __('formula.formula_delete_success'),
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
