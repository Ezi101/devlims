<?php

namespace App\Http\Controllers;

use App\GenericName;
use App\Product;
use Illuminate\Http\Request;

class GenericReportController extends Controller
{
    public function index()
    {
        $products = Product::where('product_type', 'sample')->with('generic')->groupBy('name')->get();
        return view('generic.index', compact('products'));
    }

    public function filterByDate(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $productName = $request->get('product_name');

        $query = Product::query(); // Adjust according to your model

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($productName) {
            $query->where('name', 'like', "%$productName%");
        }

        $products = $query->get();

        $data = $products->map(function ($product) {
            $genericName = '';

            // Check for related genericNames relationship
            if ($product->genericNames) {
                // Get names from the relationship
                $genericName = $product->genericNames->pluck('name')->unique()->implode(', ');
            }
            // Otherwise, check if generic_name field holds JSON data (array of IDs)
            elseif ($product->generic_name) {
                $genericIds = json_decode($product->generic_name, true);

                if (is_array($genericIds)) {
                    // Fetch names based on those IDs
                    $genericNames = GenericName::whereIn('id', $genericIds)
                        ->pluck('name')
                        ->unique()
                        ->implode(', ');
                    $genericName = $genericNames;
                }
            }

            return [
                'id' => $product->id,
                'created_at' => $product->created_at->format('M d, Y H:i:s'),
                'name' => $product->name,
                'generic_name' => $genericName
            ];
        });

        return response()->json(['data' => $data]);
    }
}
