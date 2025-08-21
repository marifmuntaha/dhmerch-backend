<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('Authorization') == 'Bearer ' . config('app.api_token')) {
            return $next($request);
        }
        return response('', Response::HTTP_UNAUTHORIZED);
    }
}
