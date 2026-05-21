<?php

namespace App\Http\Controllers;

use App\Pharmacopoeia;
use Illuminate\Http\Request;

class PharmacopoeiaController extends Controller
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
        return view('product.pharmacopoeia.pharmacopoeiacreate');
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

        try {


            $data = [
                'business_id' => $business_id,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ];

            $pharmacopoeia = Pharmacopoeia::create($data);

            return response()->json([
                'success' => 1,
                'msg' => __('Pharmacopoeia Added'),
                'pharmacopoeia' => [
                    'id' => $pharmacopoeia->id,
                    'name' => $pharmacopoeia->name,
                ]
            ]);
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Pharmacopoeia  $pharmacopoeia
     * @return \Illuminate\Http\Response
     */
    public function show(Pharmacopoeia $pharmacopoeia)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Pharmacopoeia  $pharmacopoeia
     * @return \Illuminate\Http\Response
     */
    public function edit(Pharmacopoeia $pharmacopoeia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Pharmacopoeia  $pharmacopoeia
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pharmacopoeia $pharmacopoeia)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Pharmacopoeia  $pharmacopoeia
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pharmacopoeia $pharmacopoeia)
    {
        //
    }
}
