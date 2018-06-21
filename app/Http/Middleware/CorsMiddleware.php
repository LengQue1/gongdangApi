<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        $headers = config('cors');
//
//        $response = $next($request);
//
//        foreach ($headers as $key => $value) {
//            $response->header($key, $value);
//        }
//
//        return $response;
        return $next($request)->header('Access-Control-Allow-Origin' , '*')
                                ->header('Access-Control-Allow-Methods', 'GET, HEAD, POST, PUT, DELETE', 'OPTIONS', 'PATCH')
                                ->header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization')
                                ->header('Access-Control-Allow-Credentials', 'true')
                                ->header('Access-Control-Max-Age', '60');
    }
}
