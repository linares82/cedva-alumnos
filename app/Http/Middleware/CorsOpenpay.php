<?php

namespace App\Http\Middleware;

use App\Param;
use Closure;

class CorsOpenpay
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
        //dd("openpay");
        //$parametro = Param::where('llave', 'url_api_openpay')->first();
        //dd($parametro);
        //return $next($request)->header("Access-Control-Allow-Origin", $parametro->valor);
        return $next($request);
    }
}
