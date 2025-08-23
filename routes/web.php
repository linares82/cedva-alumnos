<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FichaPagosController;
use App\Http\Controllers\InscripcionsController;
use App\Http\Controllers\User1Controller;

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


/*Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
*/
/*
Route::get('/home', 'HomeController@index')
    ->name('home')
    ->middleware('auth');
    */

Route::prefix('/')
    ->middleware('auth')
    ->controller(HomeController::class)
    ->group(function () {
        Route::get('home', 'index')->name('home');
    });

Auth::routes();

Route::get('/', [HomeController::class, 'welcome'])->name('welcome');

/*
Route::get('/', 'HomeController@welcome')
    ->name('welcome');
*/

Route::prefix('users')
    ->middleware('auth')
    ->controller(User1Controller::class)
    ->name('users.')
    ->group(function () {
        Route::get('/editPerfil/{id}', 'editPerfil')->name('editPerfil');
        Route::post('/updatePerfil', 'updatePerfil')->name('updatePerfil');
    });
/*
Route::get(
    '/users/editPerfil/{id}',
    array(
        'as' => 'users.editPerfil',
        //'middleware' => 'permission:users.editPerfil',
        'uses' => 'User1Controller@editPerfil'
    )
)->middleware('auth');
*/
/*
Route::post(
    '/users/updatePerfil',
    array(
        'as' => 'users.updatePerfil',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'User1Controller@updatePerfil'
    )
)->middleware('auth');
*/

Route::prefix('inscripcions')
    ->middleware('auth')
    ->controller(InscripcionsController::class)
    ->name('inscripcions.')
    ->group(function () {
        Route::get('/historialAcademico', 'historialAcademico')->name('historialAcademico');
        Route::get('/lista', 'lista')->name('lista');
        Route::get('/listar', 'listar')->name('listar');
    });

Route::prefix('fichaAdeudos')
    ->middleware('auth')
    ->controller(FichaPagosController::class)
    ->name('fichaAdeudos.')
    ->group(function () {
        Route::get('/datosFiscales', 'datosFiscales')->name('datosFiscales');
        Route::get('/index', 'index')->name('index');
        Route::get('/terminos', 'terminos')->name('terminos');
        Route::get('/verDetalle', 'verDetalle')->name('verDetalle');
        Route::get('/verDetalleOpenpay', 'verDetalleOpenpay')->name('verDetalleOpenpay');
        Route::get('/verDetallePaycode', 'verDetallePaycode')->name('verDetallePaycode');
        Route::get('/verDetalleMattilda', 'verDetalleMattilda')->name('verDetalleMattilda');
        Route::get('/cmbUsoFactura', 'cmbUsoFactura')->name('cmbUsoFactura');
        Route::get('/imprimir', 'imprimir')->name('imprimir');
        Route::get('/datosFactura', 'datosFactura')->name('datosFactura');
        Route::get('/getFacturaXmlByUuid', 'getFacturaXmlByUuid')->name('getFacturaXmlByUuid');
        Route::get('/getFacturaXmlByUuid40', 'getFacturaXmlByUuid40')->name('getFacturaXmlByUuid40');
        Route::get('/getFacturaPdfByUuid', 'getFacturaPdfByUuid')->name('getFacturaPdfByUuid');
        Route::get('/getFacturaPdfByUuid40', 'getFacturaPdfByUuid40')->name('getFacturaPdfByUuid40');
        Route::get('/tokenOpenpay', 'tokenOpenpay')->name('tokenOpenpay');
        Route::get('/buscarMattilda', 'buscarMattilda')->name('buscarMattilda');
        Route::post('/confirmarDatosFiscales/{id}', 'confirmarDatosFiscales')->name('confirmarDatosFiscales');
        Route::post('/verDetalleConfirmar', 'verDetalleConfirmar')->name('verDetalleConfirmar');
        Route::post('/crearCajaPagoPeticion', 'crearCajaPagoPeticion')->name('crearCajaPagoPeticion');
        Route::post('/crearCajaPagoPeticionOpenpay', 'crearCajaPagoPeticionOpenpay')->name('crearCajaPagoPeticionOpenpay');
        Route::post('/crearCajaPagoPeticionPaycode', 'crearCajaPagoPeticionPaycode')->name('crearCajaPagoPeticionPaycode');
        Route::post('/crearCajaPagoPeticionMattilda', 'crearCajaPagoPeticionMattilda')->name('crearCajaPagoPeticionMattilda');
        Route::post('/confirmarFactura/{id}', 'confirmarFactura')->name('confirmarFactura');
        Route::post('/confirmarFactura40/{id}', 'confirmarFactura40')->name('confirmarFactura40');
    });




/*

Route::get(
    'inscripcions/listar',
    array(
        'as' => 'inscripcions.listar',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'InscripcionsController@listar'
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
    '/fichaAdeudos/getFacturaPdfByUuid40',
    array(
        'as' => 'fichaAdeudos.getFacturaPdfByUuid40',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@getFacturaPdfByUuid40'
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
    '/fichaAdeudos/getFacturaPdfByUuid',
    array(
        'as' => 'fichaAdeudos.getFacturaPdfByUuid',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@getFacturaPdfByUuid'
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
    '/fichaAdeudos/getFacturaXmlByUuid',
    array(
        'as' => 'fichaAdeudos.getFacturaXmlByUuid',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@getFacturaXmlByUuid'
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

Route::post(
    '/fichaAdeudos/confirmarFactura/{id}',
    array(
        'as' => 'fichaAdeudos.confirmarFactura',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@confirmarFactura'
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
Route::get(
    '/fichaAdeudos/imprimir',
    array(
        'as' => 'fichaAdeudos.imprimir',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@imprimir'
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

Route::post(
    '/fichaAdeudos/crearCajaPagoPeticionOpenpay',
    array(
        'as' => 'fichaAdeudos.crearCajaPagoPeticionOpenpay',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@crearCajaPagoPeticionOpenpay'
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
    '/fichaAdeudos/verDetalleOpenpay',
    array(
        'as' => 'fichaAdeudos.verDetalleOpenpay',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@verDetalleOpenpay'
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
    '/fichaAdeudos/verDetalleMattilda',
    array(
        'as' => 'fichaAdeudos.verDetalleMattilda',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@verDetalleMattilda'
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
Route::get(
    '/fichaAdeudos/verDetalle',
    array(
        'as' => 'fichaAdeudos.verDetalle',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@verDetalle'
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
    '/fichaAdeudos/datosFiscales',
    array(
        'as' => 'fichaAdeudos.datosFiscales',
        //'middleware' => 'permission:users.updatePerfil',
        'uses' => 'FichaPagosController@datosFiscales'
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

*/

/*
























*/
