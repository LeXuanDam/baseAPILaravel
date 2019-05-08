<?php

namespace App\Http\Middleware;

use Closure;
use App\Helper\JONWebToken as JWT;

class checkToken
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
        $token = JWT::user();
        $response = new ResponseService();
        if($token){
            if($token->time < time()) return $response->json(false, 'Invalid token');
        }
        else return $response->json(false, 'Invalid token');
        return $next($request);
    }
}
