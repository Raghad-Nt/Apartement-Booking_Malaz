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
            $acceptLanguage = $request->header('Accept-Language');
            // Extract the primary language from Accept-Language header like 'en-US,en;q=0.9'
            // Split by comma and take the first part, then extract the language code
            $locales = explode(',', $acceptLanguage);
            $primaryLocale = trim(substr($locales[0], 0, 2)); // Get first 2 characters like 'en' from 'en-US'
            
            // Validate locale
            if (in_array($primaryLocale, ['en', 'ar'])) {
                App::setLocale($primaryLocale);
                Session::put('locale', $primaryLocale);
            }
        }

        return $next($request);
    }
}