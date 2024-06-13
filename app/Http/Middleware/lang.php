<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class lang
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale('ar'); //m دا بيخلي اللغة الديفولت عربي

        if (isset($request->language) && $request->language == 'en' ){
            app()->setLocale('en');
        }
        return $next($request);
    }
}
