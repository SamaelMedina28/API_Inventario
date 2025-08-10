<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // auth()->user() is not a method
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('api')->user()) {
            return $next($request);
        }
        return response()->json([
            'message' => 'No autenticado, no estas logueado',
            'status' => 401,
        ], 401);
    }
}
