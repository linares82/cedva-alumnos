<?php

namespace App\Http\Controllers;

use App\Cliente;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Hash;
use Illuminate\Support\Facades\View;

class User1Controller extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    private $apiToken;
    public function __construct()
    {
        $this->apiToken = uniqid(base64_encode(str_random(60)));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function editPerfil($id, User $user)
    {
        $user = $user->find($id);
        //dd($user);
        return view('auth.perfil', compact('user'));
    }

    public function updatePerfil(User $user, Request $request)
    {
        //update data
        $input = $request->all();
        //dd($input);
        $user = $user->find($input['id']);
        $user->email = $input['email'];
        if (isset($input['password1'])) {
            $user->password = Hash::make($input['password1']);
        }
        $user->update();
        return redirect()->route('home')->with('message', 'Item updated successfully.');
    }
}
