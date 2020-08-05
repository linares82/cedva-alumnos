<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\Store;
use Auth;
use Session;

class SessionExpired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected $session;
    protected $timeout = 1200;

    public function __construct(Store $session)
    {
        $this->session = $session;
    }
    public function handle($request, Closure $next)
    {
        $isLoggedIn = $request->path() != 'dashboard/logout';
        if (!session('lastActivityTime'))
            $this->session->put('lastActivityTime', time());
        elseif (time() - $this->session->get('lastActivityTime') > $this->timeout) {
            $this->session->forget('lastActivityTime');
            $cookie = cookie('intend', $isLoggedIn ? url()->current() : 'dashboard');
            //auth()->logout();
            Auth::logout();
        }
        $isLoggedIn ? $this->session->put('lastActivityTime', time()) : $this->session->forget('lastActivityTime');
        return $next($request);
    }
    /*public function handle($request, Closure $next)
    {
        return $next($request);
    }*/
}
