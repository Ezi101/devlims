<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Announcement;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list = Announcement::get();

        return view('announcement.index',get_defined_vars());
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
        if (!auth()->user()->can('announcement.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'date' => 'required',
                'announcement' => 'required',
            ]);

            $user = auth()->user();
            $businessId = $user->business_id;

            Announcement::create([
                'bussiness_id' => $businessId,
                'date' => $request->date,
                'announcement' => $request->announcement,
                'created_by' => auth()->user()->id
            ]);

            return redirect()->route('announcement.index')->with('status', ['success' => 1, 'msg' => __('Announcement Created Successfully.')]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
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
    public function edit(Request $request)
    {
        $data = Announcement::where('id',$request['announcement_id'])->first();

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if (!auth()->user()->can('announcement.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'date' => 'required',
                'announcement' => 'required',
            ]);

            Announcement::where('id',$request['announcement_id'])->update([
                'date' => $request->date,
                'announcement' => $request->announcement,
                'created_by' => auth()->user()->id
            ]);

            return redirect()->route('announcement.index')->with('status', ['success' => 1, 'msg' => __('Announcement Updated Successfully.')]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
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
        //
    }
}
