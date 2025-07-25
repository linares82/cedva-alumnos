<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::post(
    '/multipagos/successMultipagos',
    array(
        'as' => 'fichaAdeudos.successMultipagos',
        'uses' => 'FichaPagosController@successMultipagos',
    )
)->middleware('corsMultipagos');

Route::post(
    '/multipagos/successOpenpay',
    array(
        'as' => 'fichaAdeudos.successOpenpay',
        'uses' => 'FichaPagosController@successOpenpay',
    )
)->middleware('corsMultipagos');

Route::post(
    '/fichaAdeudos/webhookChargeOpenpay',
    array(
        'as' => 'fichaAdeudos.webhookChargeOpenpay',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@webhookChargeOpenpay'
    )
)->middleware('CorsOpenpay');
