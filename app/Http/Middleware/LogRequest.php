<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogRequest
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
        return $next($request);
    }
    public function terminate($request,$response){
        $this->log($request,$response);
    }

    protected function log($request,$response){
        if(config('app.env') != 'production') {
            $url = $request->fullUrl();
            $method = $request->getMethod();
            $ip = $request->getClientIp();

            $log = "{$ip}: {$method}@{$url}\n" .
                "Token : " . \App\Helper\JONWebToken::user() . " \n" .
                "Request : " . json_encode($request->all()) . " \n" .
                "Response : {$response->getContent()} \n";
            Log::info($log);
        }
    }
}
