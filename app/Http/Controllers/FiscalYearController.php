<?php

namespace App\Http\Controllers;

use App\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FiscalYearController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('others.view_fiscal_year')) {
            abort(403, 'Unauthorized action.');
        }

        $fiscal_years = FiscalYear::orderBy('start_date', 'desc')->get();
        
        return view('fiscal_years.index', compact('fiscal_years'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('others.create_fiscal_year')) {
            abort(403, 'Unauthorized action.');
        }

        return view('fiscal_years.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('others.create_fiscal_year')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $fiscal_year = FiscalYear::create([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_year' => date('Y', strtotime($request->start_date)),
                'end_year' => date('Y', strtotime($request->end_date)),
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            // If this fiscal year is set as active, deactivate others
            if ($fiscal_year->is_active) {
                FiscalYear::where('id', '!=', $fiscal_year->id)->update(['is_active' => false]);
            }

            return redirect()->route('fiscal-years.index')
                ->with('status', ['success' => 1, 'msg' => __('Fiscal year created successfully.')]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')])
                ->withInput();
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
        if (!auth()->user()->can('others.view_fiscal_year')) {
            abort(403, 'Unauthorized action.');
        }

        $fiscal_year = FiscalYear::findOrFail($id);
        return view('fiscal_years.show', compact('fiscal_year'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('others.edit_fiscal_year')) {
            abort(403, 'Unauthorized action.');
        }

        $fiscal_year = FiscalYear::findOrFail($id);
        return view('fiscal_years.edit', compact('fiscal_year'));
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
        if (!auth()->user()->can('others.edit_fiscal_year')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $fiscal_year = FiscalYear::findOrFail($id);
            
            $fiscal_year->update([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_year' => date('Y', strtotime($request->start_date)),
                'end_year' => date('Y', strtotime($request->end_date)),
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            // If this fiscal year is set as active, deactivate others
            if ($fiscal_year->is_active) {
                FiscalYear::where('id', '!=', $fiscal_year->id)->update(['is_active' => false]);
            }

            return redirect()->route('fiscal-years.index')
                ->with('status', ['success' => 1, 'msg' => __('Fiscal year updated successfully.')]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')])
                ->withInput();
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
        if (!auth()->user()->can('others.delete_fiscal_year')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $fiscal_year = FiscalYear::findOrFail($id);
            
            // Check if fiscal year is used in any contracts
            if ($fiscal_year->contracts()->count() > 0) {
                return redirect()->route('fiscal-years.index')
                    ->with('status', ['success' => 0, 'msg' => __('Cannot delete fiscal year. It is being used in contracts.')]);
            }

            $fiscal_year->delete();

            return redirect()->route('fiscal-years.index')
                ->with('status', ['success' => 1, 'msg' => __('Fiscal year deleted successfully.')]);
        } catch (\Exception $e) {
            return redirect()->route('fiscal-years.index')
                ->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    /**
     * Change active status of fiscal year
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id)
    {
        if (!auth()->user()->can('others.edit_fiscal_year')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $fiscal_year = FiscalYear::findOrFail($id);
            
            // Deactivate all fiscal years
            FiscalYear::query()->update(['is_active' => false]);
            
            // Activate the selected one
            $fiscal_year->update(['is_active' => true]);

            return redirect()->route('fiscal-years.index')
                ->with('status', ['success' => 1, 'msg' => __('Fiscal year status updated successfully.')]);
        } catch (\Exception $e) {
            return redirect()->route('fiscal-years.index')
                ->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }
}