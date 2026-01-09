<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsLoggedIn
{
public function handle(Request $request, \Closure $next)
{
   $isLoginPage = $request->is('/') || $request->is('login');

        if (!session()->has('user') && !$isLoginPage) {
            // Not logged in and trying to access protected page
            return redirect('/');
        }

        if (session()->has('user') && $isLoginPage) {
            // Already logged in and trying to access login page
            return redirect()->route('dashboard');
        }

        return $next($request);
}

}

