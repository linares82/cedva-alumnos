<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::get('/home', 'HomeController@index')
    ->name('home')
    ->middleware('auth');

Auth::routes();
//Route::get('/', 'Auth\LoginController@showLoginForm');
Route::get('/', function () {
    return view('welcome');
});


Route::get(
    '/users/editPerfil/{id}',
    array(
        'as' => 'users.editPerfil',
        //'middleware' => 'permission:users.editPerfil',
        'uses' => 'User1Controller@editPerfil'
    )
)->middleware('auth');

Route::post(
    '/users/updatePerfil',
    array(
        'as' => 'users.updatePerfil',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'User1Controller@updatePerfil'
    )
)->middleware('auth');

Route::get(
    '/fichaAdeudos/index',
    array(
        'as' => 'fichaAdeudos.index',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@index'
    )
)->middleware('auth');

Route::post(
    '/fichaAdeudos/verDetalleConfirmar',
    array(
        'as' => 'fichaAdeudos.verDetalleConfirmar',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@verDetalleConfirmar'
    )
)->middleware('auth');

Route::get(
    '/fichaAdeudos/verDetalle',
    array(
        'as' => 'fichaAdeudos.verDetalle',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@verDetalle'
    )
)->middleware('auth');

Route::post(
    '/fichaAdeudos/crearCajaPagoPeticion',
    array(
        'as' => 'fichaAdeudos.crearCajaPagoPeticion',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@crearCajaPagoPeticion'
    )
)->middleware('auth');

Route::get(
    '/fichaAdeudos/imprimir',
    array(
        'as' => 'fichaAdeudos.imprimir',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@imprimir'
    )
)->middleware('auth');
