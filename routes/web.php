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
\Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
    //echo'<pre>';
    //var_dump($query->sql);
    //var_dump($query->bindings);
    //var_dump($query->time);
    //echo'</pre>';
    // Log::info($query->sql);
    //Log::info($query->bindings);
});


Route::get('/home', 'HomeController@index')
    ->name('home')
    ->middleware('auth');

Auth::routes();
//Route::get('/', 'Auth\LoginController@showLoginForm');

/*Route::get('/', function () {
    return view('welcome');
});*/
Route::get('/', 'HomeController@welcome')
    ->name('welcome');


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

Route::get(
    '/fichaAdeudos/verDetalleOpenpay',
    array(
        'as' => 'fichaAdeudos.verDetalleOpenpay',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@verDetalleOpenpay'
    )
)->middleware('auth');

Route::get(
    '/fichaAdeudos/verDetallePaycode',
    array(
        'as' => 'fichaAdeudos.verDetallePaycode',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@verDetallePaycode'
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

Route::post(
    '/fichaAdeudos/crearCajaPagoPeticionOpenpay',
    array(
        'as' => 'fichaAdeudos.crearCajaPagoPeticionOpenpay',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@crearCajaPagoPeticionOpenpay'
    )
)->middleware('auth');

Route::post(
    '/fichaAdeudos/crearCajaPagoPeticionPaycode',
    array(
        'as' => 'fichaAdeudos.crearCajaPagoPeticionPaycode',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@crearCajaPagoPeticionPaycode'
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

Route::get(
    '/fichaAdeudos/datosFactura',
    array(
        'as' => 'fichaAdeudos.datosFactura',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@datosFactura'
    )
)->middleware('auth');

Route::post(
    '/fichaAdeudos/confirmarFactura/{id}',
    array(
        'as' => 'fichaAdeudos.confirmarFactura',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@confirmarFactura'
    )
)->middleware('auth');
Route::post(
    '/fichaAdeudos/confirmarFactura40/{id}',
    array(
        'as' => 'fichaAdeudos.confirmarFactura40',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@confirmarFactura40'
    )
)->middleware('auth');
Route::get(
    '/fichaAdeudos/datosFiscales',
    array(
        'as' => 'fichaAdeudos.datosFiscales',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@datosFiscales'
    )
)->middleware('auth');
Route::get(
    '/fichaAdeudos/cmbUsoFactura',
    array(
        'as' => 'fichaAdeudos.cmbUsoFactura',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@cmbUsoFactura'
    )
)->middleware('auth');

Route::post(
    '/fichaAdeudos/confirmarDatosFiscales/{id}',
    array(
        'as' => 'fichaAdeudos.confirmarDatosFiscales',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@confirmarDatosFiscales'
    )
)->middleware('auth');
Route::get(
    '/fichaAdeudos/getFacturaXmlByUuid',
    array(
        'as' => 'fichaAdeudos.getFacturaXmlByUuid',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@getFacturaXmlByUuid'
    )
)->middleware('auth');
Route::get(
    '/fichaAdeudos/getFacturaXmlByUuid40',
    array(
        'as' => 'fichaAdeudos.getFacturaXmlByUuid40',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@getFacturaXmlByUuid40'
    )
)->middleware('auth');
Route::get(
    '/fichaAdeudos/getFacturaPdfByUuid',
    array(
        'as' => 'fichaAdeudos.getFacturaPdfByUuid',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@getFacturaPdfByUuid'
    )
)->middleware('auth');
Route::get(
    '/fichaAdeudos/getFacturaPdfByUuid40',
    array(
        'as' => 'fichaAdeudos.getFacturaPdfByUuid40',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@getFacturaPdfByUuid40'
    )
)->middleware('auth');
Route::get(
    '/fichaAdeudos/tokenOpenpay',
    array(
        'as' => 'fichaAdeudos.tokenOpenpay',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@tokenOpenpay'
    )
)->middleware('auth');
Route::get(
    '/fichaAdeudos/terminos',
    array(
        'as' => 'fichaAdeudos.terminos',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@terminos'
    )
)->middleware('auth');
Route::get(
    'inscripcions/historialAcademico',
    array(
        'as' => 'inscripcions.historialAcademico',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'InscripcionsController@historialAcademico'
    )
)->middleware('auth');


Route::get(
    'inscripcions/lista',
    array(
        'as' => 'inscripcions.lista',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'InscripcionsController@lista'
    )
)->middleware('auth');

Route::get(
    'inscripcions/listar',
    array(
        'as' => 'inscripcions.listar',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'InscripcionsController@listar'
    )
)->middleware('auth');


Route::get(
    'inscripcions/listar',
    array(
        'as' => 'inscripcions.listar',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'InscripcionsController@listar'
    )
)->middleware('auth');
