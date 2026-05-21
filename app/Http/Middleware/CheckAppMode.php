<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class CheckAppMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->session()->get('isDemo')) {
            Config::set('database.default', 'demo');
        } else {
            Config::set('database.default', 'mysql');
        }

        return $next($request);
    }
}

