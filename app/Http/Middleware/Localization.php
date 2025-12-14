<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    
      //Handle an incoming request.
     
    public function handle(Request $request, Closure $next): Response
    {
        // Check if locale is set in session
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale', 'en'));
        }
        // Check if locale is set in header
        elseif ($request->hasHeader('Accept-Language')) {
            $locale = $request->header('Accept-Language');
            // Validate locale
            if (in_array($locale, ['en', 'ar'])) {
                App::setLocale($locale);
                Session::put('locale', $locale);
            }
        }

        return $next($request);
    }
}