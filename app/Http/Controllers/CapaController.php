<?php

namespace App\Http\Controllers;

use Auth;
use App\Capa;
use App\User;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;

class CapaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('capa.view') ) {
            abort(403, 'Unauthorized action.');
        }

        $id = Auth::user()->id;
        $markTo = Capa::where('remarkGiver', $id)->where('markTo', '1')->first();
        $remarks = Capa::with('user')->orderBy('created_at', 'desc')->get();
        $issues = Capa::count('id');
        $progress = Capa::whereIn('status', ['In Progress', 'pending'])->count('id');
        $completed = Capa::where('status', 'completed')->count('id');

        return view('capa.create_capa', compact('remarks', 'issues', 'progress', 'completed', 'markTo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!auth()->user()->can('capa.create') ) {
            abort(403, 'Unauthorized action.');
        }
        $users = User::all();
        $id=$request["device_id"];
        return view('capa.model.capa_model', get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('capa.create') ) {
            abort(403, 'Unauthorized action.');
        }

        $id = Auth::user()->id;
        $business_id = request()->session()->get('user.business_id');

        $capaData = [
            'user_id' => $request->user_id,
            'business_id' => $business_id,
            'type' => $request->type,
            'remarks' => $request->remarks,
            'remarkGiver' => $request->remarkGiver,
            'markTo' => 0,
            'device_id' => $request->equipment_id
        ];

        try {
            if (auth()->user()->username == 'superadmin' || auth()->user()->username == 'admin') {
                if (Capa::where('remarkGiver', $request->user_id)->where('status', 'pending')->exists()) {
                    $capaExist = Capa::where('remarkGiver', $request->user_id)->where('status', 'pending')->first();
                    $capa = $capaExist;
                    $capaExist->status = "completed";
                    $capaExist->markTo = 0;
                    $capaExist->update();
                    $newCapa = Capa::create($capaData);
                    AuditLogger::log('created', 'Capa', 'Capa ID: ' . $newCapa->id . ', Type: ' . $newCapa->type . ', Remarks: ' . $newCapa->remarks . ', RemarkGiver: ' . $newCapa->remarkGiver);
                } else {
                    $newCapa = Capa::create($capaData);
                    AuditLogger::log('created', 'Capa', 'Capa ID: ' . $newCapa->id . ', Type: ' . $newCapa->type . ', Remarks: ' . $newCapa->remarks . ', RemarkGiver: ' . $newCapa->remarkGiver);
                }

                return redirect()->back()->with('status', ['success' => 1, 'msg' => __('Capa Record Added')]);
            } else {
                $capa = Capa::create([
                    'user_id' => $request->user_id,
                    'business_id' => $business_id,
                    'type' => $request->type,
                    'remarks' => $request->remarks,
                    'remarkGiver' => $request->remarkGiver,
                    'markTo' => 1,
                    'device_id' => $request->equipment_id
                ]);

                AuditLogger::log('created', 'Capa', 'Capa ID: ' . $capa->id . ', Type: ' . $capa->type . ', Remarks: ' . $capa->remarks . ', RemarkGiver: ' . $capa->remarkGiver);

                return redirect()->back()->with('status', ['success' => 1, 'msg' => __('Capa Record Created Successfully')]);
            }
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Capa  $capa
     * @return \Illuminate\Http\Response
     */
    public function show(Capa $capa)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Capa  $capa
     * @return \Illuminate\Http\Response
     */
    public function edit(Capa $capa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Capa  $capa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Capa $capa)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Capa  $capa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if (!auth()->user()->can('capa.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Retrieve the Capa to be deleted
            $capa = Capa::findOrFail($request->remark);

            // Delete the Capa
            $delete = $capa->delete();

            // Log deletion event
            AuditLogger::log('deleted', 'Capa', 'Capa ID: ' . $capa->id);

            $output = [
                'success' => true,
                'msg' => __('capa.capa_deleted'),
            ];

            return $output;
        } catch (\Throwable $th) {
            return redirect()->back()->with('status', ['danger' => 1, 'msg' => __('Some Thing Went Wrong')]);
        }
    }
}
