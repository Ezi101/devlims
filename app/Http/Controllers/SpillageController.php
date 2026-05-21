<?php

namespace App\Http\Controllers;

use App\Product;
use App\Spillage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpillageController extends Controller
{
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $spillages = Spillage::where('business_id', $business_id)->get();
        return view('spillages.index', compact('spillages'));
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $standards = Product::where('business_id', $business_id)->where('product_type', 'standard')->get()->unique('name');
        $chemicals = Product::where('business_id', $business_id)->where('product_type', 'reagent')->get()->unique('name');
        return view('spillages.create', compact('chemicals', 'standards'));
    }

    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $request->validate([
                'chemical_id' => 'required',
                'spillage_remarks' => 'nullable|string',
                'spillage_quantity' => 'required|integer',
            ]);

            $data = $request->all();
            $data['business_id'] = $business_id;
            // dd($data);
            Spillage::create($data);

            return redirect()->route('spillages.index')->with('status', ['success' => 1, 'msg' => 'Details recorded successfully']);
        } catch (\Exception $e) {
            return redirect()->route('spillages.index')->with('status', ['success' => 0, 'msg' => 'Error recording details: ' . $e->getMessage()]);
        }
    }
}
