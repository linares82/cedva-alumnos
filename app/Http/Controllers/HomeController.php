<?php

namespace App\Http\Controllers;

use App\Param;
use Illuminate\Http\Request;
use Auth;

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
        if (Auth::user()->password == $param->valor) {
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
