<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileManagersController extends Controller
{
    public function index()
    {

        return view('filemanager.index');
    }
}
