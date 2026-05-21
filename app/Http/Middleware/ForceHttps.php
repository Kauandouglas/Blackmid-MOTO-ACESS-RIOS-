<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.force_https')) {
            return $next($request);
        }

        $forwardedProto = strtolower(trim(explode(',', $request->headers->get('x-forwarded-proto', ''))[0]));

        if ($request->isSecure() || $forwardedProto === 'https') {
            return $next($request);
        }

        return redirect()->secure($request->getRequestUri(), 301);
    }
}
