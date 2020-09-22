<?php

namespace App\Http\Controllers;

use App\Param;
use Illuminate\Http\Request;
use Auth;
use Hash;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $param = Param::where('llave', 'pasDefault')->first();
        //dd(Auth::user()->password . " - " . $param->valor);
        //dd(is_null(Auth::user()->remember_token));
        if (is_null(Auth::user()->remember_token)) {
            Auth::user()->remember_token = 1;
            Auth::user()->save();
            return redirect(route('users.editPerfil', Auth::user()->id));
        } else {
            return view('home');
        }
    }

    public function welcome()
    {

        return view('welcome');
    }
}
