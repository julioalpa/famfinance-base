<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceCorrectUrl
{
    /**
     * Laravel fija APP_URL como root en el boot (forceRootUrl).
     * Este middleware lo sobreescribe con el origen real del request,
     * para que los redirects funcionen tanto desde localhost:8000
     * como desde https://192.168.x.x:8443 (red hogareña).
     */
    public function handle(Request $request, Closure $next)
    {
        app('url')->forceRootUrl($request->getSchemeAndHttpHost());

        return $next($request);
    }
}
