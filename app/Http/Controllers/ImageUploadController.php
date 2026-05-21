<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function showUploadForm()
    {
        return view('upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|file|max:2048|mimes:jpeg,png,jpg,gif,svg,pdf,doc,docx,xls,xlsx,ppt,pptx'
        ]);

        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('imageckd'), $imageName);

        $imagePath = asset('imageckd/' . $imageName);

        return response()->json([
            'success' => true,
            'imagePath' => $imagePath
        ]);
    }
}
