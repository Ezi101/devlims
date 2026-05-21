<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * @retun General Setting Page
     */

    public function index()
    {
    }

    /**
     * @retun Module Setting Page
     */
    public function demo($action, Request $request)
    {
        if ($action === 'try') {
            $request->session()->put('isDemo', true);
            // Optionally, set the database to demo mode here if needed
        } elseif ($action === 'exit') {
            $request->session()->forget('isDemo');
            // Optionally, reset the database to live mode here if needed
        }

        return redirect()->back();
    }
}
