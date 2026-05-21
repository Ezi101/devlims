<?php

namespace App\Http\Controllers;

use App\Dosage;
use Illuminate\Http\Request;

class DosageController extends Controller
{
    public function create()
    {

        return view('product.dosage.dosagecreate');
    }
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        try {


            $data = [
                'business_id' => $business_id,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ];


            $dosage = Dosage::create($data);

            return response()->json([
                'success' => 1,
                'msg' => __('Dosage Added'),
                'dosage' => [
                    'id' => $dosage->id,
                    'name' => $dosage->name,
                ]
            ]);
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }
}
