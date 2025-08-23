<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FichaPagosController;

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

/*
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
        'uses' => 'FichaPagosController@webhookChargeOpenpay'
    )
)->middleware('CorsOpenpay');
*/

Route::prefix('fichaAdeudos')
    ->middleware('CorsOpenpay')
    ->controller(FichaPagosController::class)
    ->name('fichaAdeudos.')
    ->group(function () {
        Route::get('/webhookChargeOpenpay', 'webhookChargeOpenpay')->name('webhookChargeOpenpay');
    });
Route::prefix('fichaAdeudos')
    //->middleware('CorsOpenpay')
    ->controller(FichaPagosController::class)
    ->name('fichaAdeudos.')
    ->group(function () {
        Route::post('/webhookMattilda', 'webhookMattilda')->name('webhookMattilda');
    });

Route::prefix('fichaAdeudos')
    ->middleware('corsMultipagos')
    ->controller(FichaPagosController::class)
    ->name('fichaAdeudos.')
    ->group(function () {
        Route::get('/successOpenpay', 'successOpenpay')->name('successOpenpay');
    });
Route::prefix('fichaAdeudos')
    ->middleware('corsMultipagos')
    ->controller(FichaPagosController::class)
    ->name('fichaAdeudos.')
    ->group(function () {
        Route::get('/successMultipagos', 'successMultipagos')->name('successMultipagos');
    });
