<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function fetchSubcategories(Request $request)
    {
        $categoryId = $request->input('category_id');
        $subcategories = Category::where('parent_id', $categoryId)->get(['id', 'name']);

        return response()->json($subcategories);
    }
}
