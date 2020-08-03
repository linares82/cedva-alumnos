<?php

namespace App\Http\Middleware;

use App\Param;
use Closure;

class CorsMultipagos
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

        $parametro = Param::where('llave', 'url_multipagos')->first();
        //dd($parametro);
        return $next($request)->header("Access-Control-Allow-Origin", $parametro->valor);
    }
}
