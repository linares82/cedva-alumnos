<?php

namespace App\Http\Controllers;

use Mail;
use App\Caja;
use App\Pago;
use App\User;
use App\Param;
use Exception;
use XMLWriter;
use App\Adeudo;
use App\CajaLn;
use SoapClient;
use App\Cliente;
use App\Message;
use App\Plantel;
use App\Seccion;
use DOMDocument;
use App\Empleado;
use Carbon\Carbon;
use App\UsoFactura;
use App\PromoPlanLn;
use App\TipoPersona;
use SimpleXMLElement;
use App\RegimenFiscal;
use GuzzleHttp\Client;
use App\CuentasEfectivo;
use App\ImpresionTicket;
use App\PeticionOpenpay;
use App\AdeudoPagoOnLine;
use App\AutorizacionBeca;
use App\SuccessMultipago;
use Openpay\Data\Openpay;
use App\NivelEducativoSat;
use App\PeticionMultipago;
use App\CombinacionCliente;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\SerieFolioSimplificado;
use Illuminate\Http\JsonResponse;
use Openpay\Data\OpenpayApiError;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use Openpay\Data\OpenpayApiAuthError;
use Illuminate\Support\Facades\Session;
use Luecano\NumeroALetras\NumeroALetras;
use Openpay\Data\OpenpayApiRequestError;
use Openpay\Data\OpenpayApiConnectionError;
use Illuminate\Support\Facades\Notification;
use Openpay\Data\OpenpayApiTransactionError;
use App\Notifications\NotificacionErrorApiFoliosDigitales;

require_once '../vendor/autoload.php';

class FichaPagosController extends Controller
{
    public function index(Request $request)
    {
        $datos = $request->all();
        if (isset($datos['id'])) {
            $this->successOpenpay($datos['id']);
        }
        $cliente = Cliente::where('matricula', Auth::user()->name)->first();
        /*$pago=Pago::find(86721);
        $pago->fecha="2021-10-11";
        $pago->save();*/

        /*$adeudos = Adeudo::where('adeudos.cliente_id', $cliente->id)
            ->join('combinacion_clientes as cc', 'cc.id', '=', 'adeudos.combinacion_cliente_id')
            ->whereNull('cc.deleted_at')
            ->whereNull('adeudos.deleted_at')
            ->get();*/
        $combinaciones = CombinacionCliente::where('cliente_id', $cliente->id)
            ->where('cuenta_ticket_pago', '>', 0)
            ->whereNull('deleted_at')
            //->with('grado')
            ->get();
        //dd($combinaciones);
        //$this->actualizarAdeudosPagos($cliente->id, $cliente->plantel_id);
        //$this->actualizarAdeudosPagos($cliente->id, $cliente->plantel_id);
        $this->actualizarAdeudosPagos($cliente->id, $cliente->plantel_id);
        $adeudo_pago_online = AdeudoPagoOnLine::where('matricula', $cliente->matricula)
            ->orderBy('fecha_limite')
            ->whereNull('deleted_at')
            ->get();
        //dd($adeudo_pago_online->toArray());
        $secciones_validas = Seccion::all();

        return view('fichaPagos.index', compact('cliente', 'adeudo_pago_online', 'combinaciones', 'secciones_validas'));
    }

    public function actualizarAdeudosPagos($cliente, $plantel)
    {

        $plantel = Plantel::find($plantel);
        //dd($plantel);
        $conceptosValidos = $plantel->conceptoMultipagos->pluck('id');
        //dd($conceptosValidos);
        $seccionesValidas = Seccion::pluck('name');
        //dd($seccionesValidas);
        //dd($conceptosValidos);
        /*$mes = Date('m');

        switch ($mes) {
            case 1:
                $mesPasado = 10;
                $anioPasado = Date('Y') - 1;
                break;
            case 2:
                $mesPasado = 11;
                $anioPasado = Date('Y') - 1;
                break;
            case 3:
                $mesPasado = 12;
                $anioPasado = Date('Y') - 1;
                break;
            case 10:
                $mesFuturo = 1;
                $anioFuturo = Date('Y') + 1;
                break;
            case 11:
                $mesFuturo = 2;
                $anioFuturo = Date('Y') + 1;
                break;
            case 12:
                $mesFuturo = 3;
                $anioFuturo = Date('Y') + 1;
                break;
            default:
                $mesFuturo = $mes + 3;
                $mesPasado = $mes - 3;
                $anioPasado = Date('Y');
                $anioFuturo = Date('Y');
        }
        $anio = Date('Y');
        */
        $adeudos_pagados = Adeudo::select('adeudos.*', 'g.seccion')
            ->join('caja_conceptos as cajaCon', 'cajaCon.id', '=', 'adeudos.caja_concepto_id')
            ->whereIn('cajaCon.cve_multipagos', $conceptosValidos)
            ->where('adeudos.cliente_id', $cliente)
            ->where('adeudos.pagado_bnd', 1)
            ->whereNull('adeudos.deleted_at')
            ->join('combinacion_clientes as cc', 'cc.id', '=', 'adeudos.combinacion_cliente_id')
            ->join('grados as g', 'g.id', '=', 'cc.grado_id')
            //->whereIn('g.seccion', $seccionesValidas)
            ->orderBy('adeudos.fecha_pago');
        /*->whereNull('cc.deleted_at')
            ->with('caja')
            ->with('cliente')
            ->with('pagoOnLine')
            ->get();*/
        $adeudos = Adeudo::select('adeudos.*', 'g.seccion')
            ->join('caja_conceptos as cajaCon', 'cajaCon.id', '=', 'adeudos.caja_concepto_id')
            /*->whereMonth('fecha_pago', '>=', $mesPasado)
            ->whereMonth('fecha_pago', '<=', $mesFuturo)
            ->whereYear('fecha_pago', '>=', $anioPasado)
            ->whereYear('fecha_pago', '<=', $anioFuturo)*/
            ->whereIn('cajaCon.cve_multipagos', $conceptosValidos)
            ->where('adeudos.cliente_id', $cliente)
            ->where('adeudos.pagado_bnd', 0)
            ->orderBy('adeudos.fecha_pago')
            ->take(5)
            ->whereNull('adeudos.deleted_at')
            ->join('combinacion_clientes as cc', 'cc.id', '=', 'adeudos.combinacion_cliente_id')
            ->join('grados as g', 'g.id', '=', 'cc.grado_id')
            //->whereIn('g.seccion', $seccionesValidas)
            ->whereNull('cc.deleted_at')
            ->with('caja')
            ->with('cliente')
            ->with('pagoOnLine')
            ->union($adeudos_pagados)
            ->get();
        //dd($adeudos->toArray());
        foreach ($adeudos as $adeudo) {
            //Log::Info($adeudo->toArray());
            $adeudo_pago_online = optional($adeudo)->pagoOnLine;
            //$adeudo_pago_online = AdeudoPagoOnLine::where('adeudo_id', $adeudo->id)->first();
            //dd($adeudo->toArray());
            if ($adeudo->pagado_bnd == 1) {

                if (is_null($adeudo_pago_online)) {
                    //dd($adeudo->caja->pago);
                    $inputC['matricula'] = $adeudo->cliente->matricula;
                    $inputC['adeudo_id'] = $adeudo->id;
                    if ($adeudo->caja_id == 0) {
                        $inputC['pago_id'] = 0;
                        $inputC['caja_id'] = 0;
                        $inputC['subtotal'] = 0;
                        $inputC['descuento'] = 0;
                        $inputC['recargo'] = 0;
                        $inputC['total'] = 0;
                    } else {
                        $inputC['pago_id'] = $adeudo->caja->pago->id;
                        $inputC['caja_id'] = $adeudo->caja->id;
                        $inputC['subtotal'] = $adeudo->caja->subtotal;
                        $inputC['descuento'] = $adeudo->caja->descuento;
                        $inputC['recargo'] = $adeudo->caja->recargo;
                        $inputC['total'] = $adeudo->caja->total;
                    }
                    $inputC['cliente_id'] = $adeudo->cliente_id;
                    $inputC['plantel_id'] = $adeudo->cliente->plantel_id;
                    $inputC['usu_alta_id'] = 1;
                    $inputC['usu_mod_id'] = 1;

                    AdeudoPagoOnLine::create($inputC);
                } else {
                    $hoy = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
                    //dd($hoy->toDateString());
                    //dd($hoy->toDateString() != $adeudo_pago_online->created_at->toDateString());
                    //if ($hoy->toDateString() != $adeudo_pago_online->created_at->toDateString()) {
                    //$input['matricula'] = $adeudo->cliente->matricula;
                    //$input['cliente_id'] = $adeudo->cliente->id;
                    //$input['adeudo_id'] = $adeudo->id;
                    //dd($adeudo);
                    $input['pago_id'] = (optional($adeudo->caja->pago)->id <> 0 ? optional($adeudo->caja->pago)->id : 0);
                    $input['caja_id'] = (optional($adeudo->caja)->id <> 0 ? optional($adeudo->caja)->id : 0);
                    $datos_calculados = $this->predefinido($adeudo->id);
                    //dd($datos_calculados);
                    $input['subtotal'] = $datos_calculados['subtotal'];
                    $input['descuento'] = $datos_calculados['descuento'];
                    $input['recargo'] = $datos_calculados['recargo'];
                    $input['total'] = $datos_calculados['total'];
                    $input['fecha_limite'] = $datos_calculados['fecha_limite'];
                    //$input['cliente_id'] = $adeudo->cliente_id;
                    //$input['usu_alta_id'] = 1;
                    //$input['usu_mod_id'] = 1;
                    //dd($input);
                    $adeudo_pago_online->update($input);
                    //$this->actualizarRegistrosRelacionados($adeudo_pago_online->id);
                    //}
                }
            } else {

                if (is_null($adeudo_pago_online)) {
                    $inputC['matricula'] = $adeudo->cliente->matricula;
                    $inputC['adeudo_id'] = $adeudo->id;
                    $inputC['pago_id'] = (optional($adeudo->caja->pago)->id <> 0 ? optional($adeudo->caja->pago)->id : 0);
                    $inputC['caja_id'] = (optional($adeudo->caja)->id <> 0 ? optional($adeudo->caja)->id : 0);
                    $datos_calculados = $this->predefinido($adeudo->id);
                    //dd($datos_calculados);
                    $inputC['subtotal'] = $datos_calculados['subtotal'];
                    $inputC['descuento'] = $datos_calculados['descuento'];
                    $inputC['recargo'] = $datos_calculados['recargo'];
                    $inputC['total'] = $datos_calculados['total'];
                    $inputC['fecha_limite'] = $datos_calculados['fecha_limite'];
                    $inputC['cliente_id'] = $adeudo->cliente_id;
                    $inputC['plantel_id'] = $adeudo->cliente->plantel_id;
                    $inputC['usu_alta_id'] = 1;
                    $inputC['usu_mod_id'] = 1;
                    AdeudoPagoOnLine::create($inputC);
                } else {
                    $hoy = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
                    //dd($hoy->toDateString());
                    //dd($hoy->toDateString() != $adeudo_pago_online->created_at->toDateString());
                    //if ($hoy->toDateString() != $adeudo_pago_online->created_at->toDateString()) {
                    //$input['matricula'] = $adeudo->cliente->matricula;
                    //$input['cliente_id'] = $adeudo->cliente->id;
                    //$input['adeudo_id'] = $adeudo->id;
                    //dd($adeudo);
                    $input['pago_id'] = (optional($adeudo->caja->pago)->id <> 0 ? optional($adeudo->caja->pago)->id : 0);
                    $input['caja_id'] = (optional($adeudo->caja)->id <> 0 ? optional($adeudo->caja)->id : 0);
                    $datos_calculados = $this->predefinido($adeudo->id);
                    //dd($datos_calculados);
                    $input['subtotal'] = $datos_calculados['subtotal'];
                    $input['descuento'] = $datos_calculados['descuento'];
                    $input['recargo'] = $datos_calculados['recargo'];
                    $input['total'] = $datos_calculados['total'];
                    $input['fecha_limite'] = $datos_calculados['fecha_limite'];
                    //$input['cliente_id'] = $adeudo->cliente_id;
                    //$input['usu_alta_id'] = 1;
                    //$input['usu_mod_id'] = 1;
                    $adeudo_pago_online->update($input);
                    //$this->actualizarRegistrosRelacionados($adeudo_pago_online->id);
                    //}
                }
            }
        }
    }

    public function predefinido($adeudo_tomado)
    {
        $adeudo = Adeudo::with('planPagoLn')->find($adeudo_tomado);
        //dd($conceptosValidos);

        //$adeudos = Adeudo::where('id', '=', $adeudo_tomado)->get();
        //dd($adeudo);

        $cliente = Cliente::with('autorizacionBecas')->find($adeudo->cliente_id);
        //dd($adeudos->toArray());

        //foreach ($adeudos as $adeudo) {
        $caja_ln['caja_concepto_id'] = $adeudo->caja_concepto_id;
        $caja_ln['subtotal'] = $adeudo->monto;
        $caja_ln['total'] = 0;
        $caja_ln['recargo'] = 0;
        $caja_ln['descuento'] = 0;
        $caja_ln['fecha_limite'] = "";

        //Realiza descuento para inscripciones
        if (
            isset(optional($adeudo->descuento)->id) and
            ($adeudo->caja_concepto_id == 1 or $adeudo->caja_concepto_id == 23 or $adeudo->caja_concepto_id == 25)
        ) {
            $caja_ln['descuento'] = $caja_ln['subtotal'] * $adeudo->descuento->porcentaje;
        } else {
            //********************************* */
            //Calcula descuento por beca
            //********************************* */
            $beca_a = 0;
            foreach ($cliente->autorizacionBecas as $beca) {
                //dd(is_null($beca->deleted_at));
                if ($beca->bnd_tiene_vigencia == 1 and !is_null($beca->vigencia)) {
                    $fechaAdeudo = Carbon::createFromFormat('Y-m-d', $adeudo->fecha_pago);
                    $fechaVigenciaBeca = Carbon::createFromFormat('Y-m-d', $beca->vigencia);
                    if ($fechaAdeudo->lessThanOrEqualTo($fechaVigenciaBeca)) {
                        $beca_a = $beca->id;
                    }
                } elseif ($beca->bnd_tiene_vigencia == 0 and is_null($beca->vigencia)) {
                    $mesAdeudo = Carbon::createFromFormat('Y-m-d', $adeudo->fecha_pago)->month;
                    $anioAdeudo = Carbon::createFromFormat('Y-m-d', $adeudo->fecha_pago)->year;
                    $mesInicio = Carbon::createFromFormat('Y-m-d', $beca->lectivo->inicio)->month;
                    $anioInicio = Carbon::createFromFormat('Y-m-d', $beca->lectivo->inicio)->year;
                    $mesFin = Carbon::createFromFormat('Y-m-d', $beca->lectivo->fin)->month;
                    $anioFin = Carbon::createFromFormat('Y-m-d', $beca->lectivo->fin)->year;


                    if (
                        (($beca->lectivo->inicio <= $adeudo->fecha_pago and $beca->lectivo->fin >= $adeudo->fecha_pago) or
                            (($anioInicio == $anioAdeudo or $mesInicio <= $mesAdeudo) and ($anioFin == $anioAdeudo and $mesFin >= $mesAdeudo)) or
                            (($anioInicio == $anioAdeudo or $mesInicio <= $mesAdeudo) and ($anioFin > $anioAdeudo)) or
                            (($anioInicio < $anioAdeudo) and ($anioFin == $anioAdeudo and $mesFin >= $mesAdeudo))) and
                        $beca->aut_dueno == 4 and is_null($beca->deleted_at)
                    ) {
                        $beca_a = $beca->id;
                        //dd($beca);
                    }
                }
            }
            //dd($caja_ln);
            $beca_autorizada = AutorizacionBeca::find($beca_a);
            //dd($beca_autorizada);
            if (
                optional($beca_autorizada)->monto_mensualidad > 0 and
                $adeudo->cajaConcepto->bnd_mensualidad == 1 and
                ($adeudo->bnd_eximir_descuento_beca == 0 or is_null($adeudo->bnd_eximir_descuento_beca))
            ) {
                $calculo_monto_mensualidad = $caja_ln['subtotal'] * $beca->monto_mensualidad;
                $caja_ln['descuento'] = $caja_ln['descuento'] + $calculo_monto_mensualidad;
                $caja_ln['total'] = $caja_ln['subtotal'] - $caja_ln['descuento'];
            } else {
                $caja_ln['total'] = $caja_ln['subtotal'] - $caja_ln['descuento'];
            }

            //dd($caja_ln);
            //********************************* */
            //Fin Calculo descuento por beca
            //********************************* */

            //********************************* */
            //calcula descuento segun promocion ligada a
            //la linea del plan considerando la fecha de pago de la
            //inscripcion del cliente
            //********************************* */
            try {
                $promociones = $adeudo->planPagoLn->promoPlanLns;
                //PromoPlanLn::where('plan_pago_ln_id', $adeudo->plan_pago_ln_id)->get();
                $caja_ln['promo_plan_ln_id'] = 0;
                //if ($beca_a == 0 and $adeudo->bnd_eximir_descuentos == 0) {
                if ($adeudo->bnd_eximir_descuentos == 0 or is_null($adeudo->bnd_eximir_descuentos)) {
                    foreach ($promociones as $promocion) {

                        $inicio = Carbon::createFromFormat('Y-m-d', $promocion->fec_inicio);
                        $fin = Carbon::createFromFormat('Y-m-d', $promocion->fec_fin);
                        $hoy = Carbon::createFromFormat('Y-m-d', Date('Y-m-d'));
                        $monto_promocion = 0;
                        //dd($hoy);
                        if ($inicio->lessThanOrEqualTo($hoy) and $fin->greaterThanOrEqualTo($hoy) and $caja_ln['promo_plan_ln_id'] == 0) {

                            $monto_promocion = $promocion->descuento * $caja_ln['total'];
                            $caja_ln['descuento'] = $caja_ln['descuento'] + $monto_promocion;
                            $caja_ln['total'] = $caja_ln['subtotal'] - $caja_ln['descuento'];

                            $caja_ln['promo_plan_ln_id'] = $promocion->id;
                            $caja_ln['fecha_limite'] = $promocion->fec_fin;
                        }
                        //}
                    }
                }
            } catch (Exception $e) {
                dd($e);
            }
            //********************************* */
            //Fin calculo descuento por promocion
            //********************************* */


            //********************************* */
            //Calcula regla descuento recargo
            //********************************* */
            $regla_recargo = 0;
            $regla_descuento = 0;
            //dd($caja_ln);
            //dd($adeudo->planPagoLn->reglaRecargos->toArray());
            foreach ($adeudo->planPagoLn->reglaRecargos as $regla) {
                if ($adeudo->bnd_eximir_descuento_regla == 0 or is_null($adeudo->bnd_eximir_descuento_regla)) {
                    //dd($adeudo->planPagoLn->reglaRecargos->toArray());
                    $fecha_caja = Carbon::createFromFormat('Y-m-d', Date('Y-m-d'));
                    $fecha_adeudo = Carbon::createFromFormat('Y-m-d', $adeudo->fecha_pago);
                    //dd($fecha_caja->greaterThanOrEqualTo($fecha_adeudo));
                    //if ($fecha_caja >= $fecha_adeudo) {
                    if ($fecha_caja->greaterThanOrEqualTo($fecha_adeudo)) {
                        $dias = $fecha_caja->diffInDays($fecha_adeudo);
                        //dd($dias);
                        if ($fecha_caja < $fecha_adeudo) {
                            $dias = $dias * -1;
                        }
                        //dd($dias);

                        //calcula recargo o descuento segun regla y aplica
                        //dd($dias >= $regla->dia_inicio and $dias <= $regla->dia_fin);
                        if ($dias >= $regla->dia_inicio and $dias <= $regla->dia_fin) {
                            //dd($fecha_adeudo);
                            if ($regla->dia_fin > 90) {
                                $caja_ln['fecha_limite'] = $fecha_adeudo->addDay(90)->toDateString();
                            } else {
                                $caja_ln['fecha_limite'] = $fecha_adeudo->addDay($regla->dia_fin - 1)->toDateString();
                            }

                            if ($regla->tipo_regla_id == 1) {
                                //dd($regla->porcentaje);

                                if ($regla->porcentaje > 0) {
                                    //dd($regla->porcentaje);
                                    $regla_recargo = $caja_ln['subtotal'] * $regla->porcentaje;
                                    $caja_ln['recargo'] = $caja_ln['recargo'] + $regla_recargo;
                                    //$caja_ln['recargo'] = $adeudo->monto * $regla->porcentaje;
                                    //echo $caja_ln['recargo'];
                                } else {
                                    if ($adeudo->bnd_eximir_descuento_regla == 0) {
                                        $regla_descuento = $caja_ln['subtotal'] * $regla->porcentaje * -1;
                                        $caja_ln['descuento'] = $caja_ln['descuento'] + $regla_descuento;
                                        $caja_ln['total'] = $caja_ln['total'] - $caja_ln['descuento'];

                                        //$caja_ln['descuento'] = $adeudo->monto * $regla->porcentaje * -1;
                                        //echo $caja_ln['descuento'];
                                    }
                                }
                            } elseif ($regla->tipo_regla_id == 2) {
                                //dd($regla->porcentaje);
                                if ($regla->monto > 0) {
                                    $regla_recargo = $regla->monto;
                                    $caja_ln['recargo'] = $caja_ln['recargo'] + $regla_recargo;
                                    //$caja_ln['recargo'] = $regla->monto;
                                } else {
                                    if ($adeudo->bnd_eximir_descuento_regla == 0) {
                                        $regla_descuento = $regla->monto * -1;
                                        $caja_ln['descuento'] = $caja_ln['descuento'] + $regla_descuento;
                                        $caja_ln['total'] = $caja_ln['subtotal'] - $caja_ln['descuento'];

                                        //$caja_ln['descuento'] = $regla->monto * -1;
                                    }
                                }
                            }
                        }
                    } else {
                        $dias = $fecha_caja->diffInDays($fecha_adeudo);
                        if ($fecha_caja < $fecha_adeudo) {
                            $dias = $dias * -1;
                        }
                        //dd($dias);

                        //calcula recargo o descuento segun regla y aplica
                        //dd($dias >= $regla->dia_inicio and $dias <= $regla->dia_fin);
                        if ($dias >= $regla->dia_inicio and $dias <= $regla->dia_fin) {
                            if ($regla->dia_fin > 60) {
                                $caja_ln['fecha_limite'] = $fecha_adeudo->addDay(60)->toDateString();
                            } else {
                                $caja_ln['fecha_limite'] = $fecha_adeudo->addDay($regla->dia_fin - 1)->toDateString();
                            }
                            if ($regla->tipo_regla_id == 1) {
                                //dd($regla->porcentaje);

                                if ($regla->porcentaje > 0) {
                                    //dd($regla->porcentaje);
                                    $regla_recargo = $adeudo->monto * $regla->porcentaje;
                                    $caja_ln['recargo'] = $caja_ln['recargo'] + $regla_recargo;
                                    //$caja_ln['recargo'] = $adeudo->monto * $regla->porcentaje;
                                    //echo $caja_ln['recargo'];
                                } else {
                                    if ($adeudo->bnd_eximir_descuento_regla == 0) {
                                        $regla_descuento = $caja_ln['subtotal'] * $regla->porcentaje * -1;
                                        $caja_ln['descuento'] = $caja_ln['descuento'] + $regla_descuento;
                                        $caja_ln['total'] = $caja_ln['subtotal'] - $caja_ln['descuento'];

                                        //$caja_ln['descuento'] = $adeudo->monto * $regla->porcentaje * -1;
                                        //echo $caja_ln['descuento'];
                                    }
                                }
                            } elseif ($regla->tipo_regla_id == 2) {
                                //dd($regla->porcentaje);
                                if ($regla->monto > 0) {
                                    $regla_recargo = $regla->monto;
                                    $caja_ln['recargo'] = $caja_ln['recargo'] + $regla_recargo;
                                    //$caja_ln['recargo'] = $regla->monto;
                                } else {
                                    if ($adeudo->bnd_eximir_descuento_regla == 0) {
                                        $regla_descuento = $regla->monto * -1;
                                        $caja_ln['descuento'] = $caja_ln['descuento'] + $regla_descuento;
                                        $caja_ln['total'] = $caja_ln['subtotal'] - $caja_ln['descuento'];

                                        //$caja_ln['descuento'] = $regla->monto * -1;
                                    }
                                }
                            }
                        }
                    }
                }
            } //end regla recargo descuento


            //********************************* */
            //Fin calculo descuento por regla
            //********************************* */


        }
        //dd($caja_ln);
        if (!isset($caja_ln['fecha_limite']) or $caja_ln['fecha_limite'] == "") {
            //dd($caja_ln);
            $fecha_aux = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
            $fechaAdeudo = Carbon::createFromFormat('Y-m-d', $adeudo->fecha_pago);
            $dia = $fecha_aux->day;
            $mes = $fecha_aux->month;
            if (($dia >= 1 and $dia <= 10 and $mes == $fechaAdeudo->month) or
                ($dia >= 28 and $dia <= 31 and $mes <> $fechaAdeudo->month and $fechaAdeudo->lessThanOrEqualTo($fecha_aux)) or
                $fechaAdeudo->greaterThanOrEqualTo($fecha_aux)
            ) {
                $caja_ln['fecha_limite'] = Carbon::createFromFormat('Y-m-d', $adeudo->fecha_pago)->addDay(9)->toDateString();
            } else {
                $caja_ln['fecha_limite'] = date('Y-m-d');
            }
        }
        //dd($caja_ln);
        $caja_ln['total'] = $caja_ln['subtotal'] + $caja_ln['recargo'] - $caja_ln['descuento'];

        $caja_ln['adeudo_id'] = $adeudo->id;
        $caja_ln['usu_alta_id'] = Auth::user()->id;
        $caja_ln['usu_mod_id'] = Auth::user()->id;

        $caja_ln['subtotal'] = round($caja_ln['subtotal'], 0);
        $caja_ln['total'] = round($caja_ln['total'], 0);
        $caja_ln['recargo'] = round($caja_ln['recargo'], 0);
        $caja_ln['descuento'] = round($caja_ln['descuento'], 0);

        return $caja_ln;

        //}
    }

    public function verDetalleOpenpay(Request $request)
    {
        $datos = $request->all();

        //dd($navegador."-".$dispositivo);

        $adeudo_pago_online = AdeudoPagoOnLine::find($datos['adeudo_pago_online_id']);
        $plantel = Plantel::find($adeudo_pago_online->adeudo->cliente->plantel_id);
        $forma_pagos = $plantel->formaPagos()->whereNotNull('forma_pagos.cve_multipagos')->pluck('name', 'id');
        //dd($plantel);
        return view('fichaPagos.detalle_openpay', compact('adeudo_pago_online', 'forma_pagos'));
    }

    public function verDetalle(Request $request)
    {
        $datos = $request->all();

        //dd($navegador."-".$dispositivo);

        $adeudo_pago_online = AdeudoPagoOnLine::find($datos['adeudo_pago_online_id']);
        $plantel = Plantel::find($adeudo_pago_online->adeudo->cliente->plantel_id);
        $forma_pagos = $plantel->formaPagos()->whereNotNull('forma_pagos.cve_multipagos')->pluck('name', 'id');
        //dd($forma_pagos);
        return view('fichaPagos.detalle', compact('adeudo_pago_online', 'forma_pagos'));
    }

    public function crearCajaPagoPeticion(Request $request)
    {
        $datos = $request->all();
        //dd($datos);
        $adeudo_pago_online = AdeudoPagoOnLine::with('cliente')
            ->with('caja')
            ->with('pago')
            ->with('peticionMultipago')
            ->find($datos['adeudo_pago_online_id']);
        $plantel = Plantel::find($adeudo_pago_online->plantel_id);

        //Se crea registro de caja si no tiene
        if ($adeudo_pago_online->caja_id == 0 or is_null($adeudo_pago_online->caja_id)) {
            $inputCaja['cliente_id'] = $adeudo_pago_online->cliente->id;
            $inputCaja['plantel_id'] = $adeudo_pago_online->cliente->plantel->id;
            $inputCaja['subtotal'] = $adeudo_pago_online->subtotal;
            $inputCaja['descuento'] = $adeudo_pago_online->descuento;
            $inputCaja['recargo'] = $adeudo_pago_online->recargo;
            $inputCaja['total'] = $adeudo_pago_online->total;
            $inputCaja['forma_pago_id'] = $datos['forma_pago_id'];
            $inputCaja['fecha'] = date('Y-m-d');
            $inputCaja['st_caja_id'] = 0;
            $inputCaja['usu_alta_id'] = 1;
            $inputCaja['usu_mod_id'] = 1;
            $consecutivo = ++$plantel->consecutivo;
            $plantel->save();
            $inputCaja['consecutivo'] = $consecutivo;
            $caja = Caja::create($inputCaja);
            $adeudo_pago_online->caja_id = $caja->id;
            $adeudo_pago_online->save();
            $adeudo = $adeudo_pago_online->adeudo;
            $adeudo->caja_id = $caja->id;
            $adeudo->save();
        } else {
            $caja = $adeudo_pago_online->caja;
            //Caja::find($adeudo_pago_online->caja_id);
            $inputCaja['subtotal'] = $adeudo_pago_online->subtotal;
            $inputCaja['descuento'] = $adeudo_pago_online->descuento;
            $inputCaja['recargo'] = $adeudo_pago_online->recargo;
            $inputCaja['total'] = $adeudo_pago_online->total;
            $inputCaja['forma_pago_id'] = $datos['forma_pago_id'];
            $inputCaja['fecha'] = date('Y-m-d');
            $caja->update($inputCaja);
        }


        //Se crea linea de caja si no la tiene
        if ($adeudo_pago_online->caja_ln_id == 0 or is_null($adeudo_pago_online->caja_ln_id)) {
            $inputCajaLn['caja_id'] = $caja->id;
            $inputCajaLn['caja_concepto_id'] = $adeudo_pago_online->adeudo->caja_concepto_id;
            $inputCajaLn['subtotal'] = $adeudo_pago_online->subtotal;
            $inputCajaLn['descuento'] = $adeudo_pago_online->descuento;
            $inputCajaLn['recargo'] = $adeudo_pago_online->recargo;
            $inputCajaLn['total'] = $adeudo_pago_online->total;
            $inputCajaLn['adeudo_id'] = $adeudo_pago_online->adeudo_id;
            $inputCajaLn['usu_alta_id'] = 1;
            $inputCajaLn['usu_mod_id'] = 1;
            $cajaLn = CajaLn::create($inputCajaLn);
            $adeudo_pago_online->caja_ln_id = $cajaLn->id;
            $adeudo_pago_online->save();
        } else {
            $cajaLn = $adeudo_pago_online->cajaLn;
            //CajaLn::find($adeudo_pago_online->caja_ln_id);
            $inputCajaLn['subtotal'] = $adeudo_pago_online->subtotal;
            $inputCajaLn['descuento'] = $adeudo_pago_online->descuento;
            $inputCajaLn['recargo'] = $adeudo_pago_online->recargo;
            $inputCajaLn['total'] = $adeudo_pago_online->total;
            $cajaLn->update($inputCajaLn);
        }


        //Se crea registro de pago si no lo tiene
        if ($adeudo_pago_online->pago_id == 0 or is_null($adeudo_pago_online->pago_id)) {
            $inputPago['caja_id'] = $caja->id;
            $inputPago['monto'] = $caja->total;
            $inputPago['fecha'] = $caja->fecha;
            $inputPago['forma_pago_id'] = $caja->forma_pago_id;
            $inputPago['bnd_pagado'] = 0;
            $inputPago['bnd_referenciado'] = 1;
            $inputPago['usu_alta_id'] = 1;
            $inputPago['usu_mod_id'] = 1;

            $consecutivo = ++$plantel->consecutivo_pago;
            $plantel->save();
            $inputPago['consecutivo'] = $consecutivo;

            $inputPago['cuenta_efectivo_id'] = $this->getCuentasPlantelFormaPago($caja->forma_pago_id, $caja->plantel_id);

            if ($inputPago['forma_pago_id'] == 1) {
                $cuenta_efectivo = CuentasEfectivo::find($inputPago['cuenta_efectivo_id']);
                $cuenta_efectivo->csc_efectivo = $cuenta_efectivo->csc_efectivo + 1;
                $cuenta_efectivo->save();
                $input['referencia'] = $cuenta_efectivo->csc_efectivo;
            }
            $pago = Pago::create($inputPago);

            $adeudo_pago_online->pago_id = $pago->id;
            $adeudo_pago_online->save();
        } else {
            $pago = $adeudo_pago_online->pago;
            //Pago::find($adeudo_pago_online->pago_id);
            $inputPago['monto'] = $caja->total;
            $inputPago['fecha'] = $caja->fecha;
            $inputPago['forma_pago_id'] = $caja->forma_pago_id;
            $inputPago['cuenta_efectivo_id'] = $this->getCuentasPlantelFormaPago($caja->forma_pago_id, $caja->plantel_id);
            $pago->update($inputPago);
        }

        //Se genera el registro peticion de pago
        if ($adeudo_pago_online->peticion_multipago_id == 0 or is_null($adeudo_pago_online->peticion_multipago_id)) {
            $datosMultipagos = array();
            $datosMultipagos['pago_id'] = $pago->id;
            $parametros = Param::where('llave', 'mp_account')->first();
            $datosMultipagos['mp_account'] = $parametros->valor;
            $datosMultipagos['mp_product'] = $cajaLn->cajaConcepto->cve_multipagos;
            $datosMultipagos['mp_order'] = $this->formatoDato('000', $caja->plantel_id) . $this->formatoDato('000000000', $caja->id) . $this->formatoDato('000000', $caja->consecutivo);
            $datosMultipagos['mp_reference'] = $this->formatoDato('000', $caja->plantel_id) . $this->formatoDato('000000000', $pago->id) . $this->formatoDato('000000', $pago->consecutivo);

            $datosMultipagos['mp_node'] = $plantel->cve_multipagos; //VAlor depente del plantel por ahora default
            $datosMultipagos['mp_concept'] = 1; //Valor depende del caja_conceptos por ahora default

            $datosMultipagos['mp_amount'] = number_format((float) $pago->monto, 2, '.', '');
            //$cliNombre = $caja->cliente->nombre . " " . $caja->cliente->nombre2 . " " . $caja->cliente->ape_paterno . " " . $caja->cliente->ape_materno;
            $datosMultipagos['mp_customername'] = substr($datos['pagador'], 0, 50);
            $datosMultipagos['mp_currency'] = 1;
            $cadenaCifrar = $datosMultipagos['mp_order'] . $datosMultipagos['mp_reference'] . $datosMultipagos['mp_amount'];
            $parametros = Param::where('llave', 'cifrado_multipagos')->first();
            $datosMultipagos['mp_signature'] = hash_hmac('sha256', $cadenaCifrar, $parametros->valor);
            $parametros = Param::where('llave', 'url_success_multipagos')->first();
            $datosMultipagos['mp_urlsuccess'] = url($parametros->valor);
            $parametros = Param::where('llave', 'url_fail_multipagos')->first();
            $datosMultipagos['mp_urlfailure'] = url($parametros->valor);
            $datosMultipagos['usu_alta_id'] = 1;
            $datosMultipagos['usu_mod_id'] = 1;
            $parametros = Param::where('llave', 'url_multipagos')->first();
            $datosMultipagos['url_peticion'] = $parametros->valor;
            $datosMultipagos['mp_paymentmethod'] = $pago->formaPago->cve_multipagos;
            $datosMultipagos['mp_datereference'] = $adeudo_pago_online->fecha_limite->toDateString();
            $datosMultipagos['navegador'] = $this->getBrowser($_SERVER['HTTP_USER_AGENT']);
            $datosMultipagos['dispositivo'] = $this->getDispositivo();

            //dd($datosMultipagos);
            $peticion_multipagos = PeticionMultipago::create($datosMultipagos);

            //Se actualizan los datos en el registro de pagos en linea
            $adeudo_pago_online->peticion_multipago_id = $peticion_multipagos->id;
            $adeudo_pago_online->save();
        } else {
            $peticion_multipagos = $adeudo_pago_online->peticionMultipago;
            $peticion_multipagos->contador_peticiones++;
            $peticion_multipagos->save();
            //PeticionMultipago::find($adeudo_pago_online->peticion_multipago_id);

            $parametros = Param::where('llave', 'mp_account')->first();
            $datosMultipagos['mp_account'] = $parametros->valor;
            $datosMultipagos['mp_product'] = $cajaLn->cajaConcepto->cve_multipagos;
            $datosMultipagos['mp_order'] = $this->formatoDato('000', $caja->plantel_id) . $this->formatoDato('000000000', $caja->id) . $this->formatoDato('000000', $caja->consecutivo);
            $datosMultipagos['mp_reference'] = $this->formatoDato('000', $caja->plantel_id) . $this->formatoDato('000000000', $pago->id) . $this->formatoDato('000000', $pago->consecutivo);

            $datosMultipagos['mp_node'] = $plantel->cve_multipagos; //VAlor depente del plantel por ahora default
            $datosMultipagos['mp_concept'] = 1; //Valor depende del caja_conceptos por ahora default

            $datosMultipagos['mp_amount'] = number_format((float) $pago->monto, 2, '.', '');
            //$cliNombre = $caja->cliente->nombre . " " . $caja->cliente->nombre2 . " " . $caja->cliente->ape_paterno . " " . $caja->cliente->ape_materno;
            $datosMultipagos['mp_customername'] = substr($datos['pagador'], 0, 50);
            $datosMultipagos['mp_currency'] = 1;
            $cadenaCifrar = $datosMultipagos['mp_order'] . $datosMultipagos['mp_reference'] . $datosMultipagos['mp_amount'];
            $parametros = Param::where('llave', 'cifrado_multipagos')->first();
            $datosMultipagos['mp_signature'] = hash_hmac('sha256', $cadenaCifrar, $parametros->valor);
            $parametros = Param::where('llave', 'url_success_multipagos')->first();
            $datosMultipagos['mp_urlsuccess'] = url($parametros->valor);
            $parametros = Param::where('llave', 'url_fail_multipagos')->first();
            $datosMultipagos['mp_urlfailure'] = url($parametros->valor);
            $datosMultipagos['usu_alta_id'] = 1;
            $datosMultipagos['usu_mod_id'] = 1;
            $parametros = Param::where('llave', 'url_multipagos')->first();
            $datosMultipagos['url_peticion'] = $parametros->valor;
            $datosMultipagos['mp_paymentmethod'] = $pago->formaPago->cve_multipagos;
            //dd($adeudo_pago_online);
            $datosMultipagos['mp_datereference'] = $adeudo_pago_online->fecha_limite->toDateString();
            $datosMultipagos['navegador'] = $this->getBrowser($_SERVER['HTTP_USER_AGENT']);
            $datosMultipagos['dispositivo'] = $this->getDispositivo();

            $peticion_multipagos->update($datosMultipagos);
            //dd($peticion_multipagos);
        }

        return response()->json([
            'datos' => $datosMultipagos,
        ], 200);
    }

    public function crearCajaPagoPeticionOpenpay(Request $request)
    {
        $datos = $request->all();
        //dd($datos);
        $adeudo_pago_online = AdeudoPagoOnLine::with('cliente')
            ->with('caja')
            ->with('pago')
            ->with('peticionMultipago')
            ->find($datos['adeudo_pago_online_id']);
        $plantel = Plantel::find($adeudo_pago_online->plantel_id);


        $existePeticionOpenpayBancos = PeticionOpenpay::where('pago_id', $adeudo_pago_online->pago_id)
            ->where('pmethod', 'bank_account')
            //->whereNotNull('rid')
            ->first();
        //dd(!is_null($existePeticionOpenpayBancos));
        if (!is_null($existePeticionOpenpayBancos)) {
            $resultado = $this->pagoBancoExistente($existePeticionOpenpayBancos, $plantel);
            return response()->json($resultado);
        }


        //Se crea registro de caja si no tiene
        if ($adeudo_pago_online->caja_id == 0 or is_null($adeudo_pago_online->caja_id)) {
            $inputCaja['cliente_id'] = $adeudo_pago_online->cliente->id;
            $inputCaja['plantel_id'] = $adeudo_pago_online->cliente->plantel->id;
            $inputCaja['subtotal'] = $adeudo_pago_online->subtotal;
            $inputCaja['descuento'] = $adeudo_pago_online->descuento;
            $inputCaja['recargo'] = $adeudo_pago_online->recargo;
            $inputCaja['total'] = $adeudo_pago_online->total;
            $inputCaja['forma_pago_id'] = $datos['forma_pago_id'];
            $inputCaja['fecha'] = date('Y-m-d');
            $inputCaja['st_caja_id'] = 0;
            $inputCaja['usu_alta_id'] = 1;
            $inputCaja['usu_mod_id'] = 1;
            $consecutivo = ++$plantel->consecutivo;
            $plantel->save();
            $inputCaja['consecutivo'] = $consecutivo;
            $caja = Caja::create($inputCaja);
            $adeudo_pago_online->caja_id = $caja->id;
            $adeudo_pago_online->save();
            $adeudo = $adeudo_pago_online->adeudo;
            $adeudo->caja_id = $caja->id;
            $adeudo->save();
        } else {
            $caja = $adeudo_pago_online->caja;
            //Caja::find($adeudo_pago_online->caja_id);
            $inputCaja['subtotal'] = $adeudo_pago_online->subtotal;
            $inputCaja['descuento'] = $adeudo_pago_online->descuento;
            $inputCaja['recargo'] = $adeudo_pago_online->recargo;
            $inputCaja['total'] = $adeudo_pago_online->total;
            $inputCaja['forma_pago_id'] = $datos['forma_pago_id'];
            $inputCaja['fecha'] = date('Y-m-d');
            $caja->update($inputCaja);
        }


        //Se crea linea de caja si no la tiene
        if ($adeudo_pago_online->caja_ln_id == 0 or is_null($adeudo_pago_online->caja_ln_id)) {
            $inputCajaLn['caja_id'] = $caja->id;
            $inputCajaLn['caja_concepto_id'] = $adeudo_pago_online->adeudo->caja_concepto_id;
            $inputCajaLn['subtotal'] = $adeudo_pago_online->subtotal;
            $inputCajaLn['descuento'] = $adeudo_pago_online->descuento;
            $inputCajaLn['recargo'] = $adeudo_pago_online->recargo;
            $inputCajaLn['total'] = $adeudo_pago_online->total;
            $inputCajaLn['adeudo_id'] = $adeudo_pago_online->adeudo_id;
            $inputCajaLn['usu_alta_id'] = 1;
            $inputCajaLn['usu_mod_id'] = 1;
            $cajaLn = CajaLn::create($inputCajaLn);
            $adeudo_pago_online->caja_ln_id = $cajaLn->id;
            $adeudo_pago_online->save();
        } else {
            $cajaLn = $adeudo_pago_online->cajaLn;
            //CajaLn::find($adeudo_pago_online->caja_ln_id);
            $inputCajaLn['subtotal'] = $adeudo_pago_online->subtotal;
            $inputCajaLn['descuento'] = $adeudo_pago_online->descuento;
            $inputCajaLn['recargo'] = $adeudo_pago_online->recargo;
            $inputCajaLn['total'] = $adeudo_pago_online->total;
            $cajaLn->update($inputCajaLn);
        }


        //Se crea registro de pago si no lo tiene
        if ($adeudo_pago_online->pago_id == 0 or is_null($adeudo_pago_online->pago_id)) {
            $inputPago['caja_id'] = $caja->id;
            $inputPago['monto'] = $caja->total;
            $inputPago['fecha'] = $caja->fecha;
            $inputPago['forma_pago_id'] = $caja->forma_pago_id;
            $inputPago['bnd_pagado'] = 0;
            $inputPago['bnd_referenciado'] = 1;
            $inputPago['usu_alta_id'] = 1;
            $inputPago['usu_mod_id'] = 1;

            $consecutivo = ++$plantel->consecutivo_pago;
            $plantel->save();
            $inputPago['consecutivo'] = $consecutivo;

            $inputPago['cuenta_efectivo_id'] = $this->getCuentasPlantelFormaPago($caja->forma_pago_id, $caja->plantel_id);

            if ($inputPago['forma_pago_id'] == 1) {
                $cuenta_efectivo = CuentasEfectivo::find($inputPago['cuenta_efectivo_id']);
                $cuenta_efectivo->csc_efectivo = $cuenta_efectivo->csc_efectivo + 1;
                $cuenta_efectivo->save();
                $input['referencia'] = $cuenta_efectivo->csc_efectivo;
            }
            $pago = Pago::create($inputPago);

            $adeudo_pago_online->pago_id = $pago->id;
            $adeudo_pago_online->save();
        } else {
            $pago = $adeudo_pago_online->pago;
            //Pago::find($adeudo_pago_online->pago_id);
            $inputPago['monto'] = $caja->total;
            $inputPago['fecha'] = $caja->fecha;
            $inputPago['forma_pago_id'] = $caja->forma_pago_id;
            $inputPago['cuenta_efectivo_id'] = $this->getCuentasPlantelFormaPago($caja->forma_pago_id, $caja->plantel_id);
            $pago->update($inputPago);
        }

        //Se genera el registro peticion de pago
        /*if ($adeudo_pago_online->peticion_multipago_id == 0 or is_null($adeudo_pago_online->peticion_multipago_id)) {
            $datosMultipagos = array();
            $datosMultipagos['pago_id'] = $pago->id;
            $parametros = Param::where('llave', 'mp_account')->first();
            $datosMultipagos['mp_account'] = $parametros->valor;
            $datosMultipagos['mp_product'] = $cajaLn->cajaConcepto->cve_multipagos;
            $datosMultipagos['mp_order'] = $this->formatoDato('000', $caja->plantel_id) . $this->formatoDato('000000000', $caja->id) . $this->formatoDato('000000', $caja->consecutivo);
            $datosMultipagos['mp_reference'] = $this->formatoDato('000', $caja->plantel_id) . $this->formatoDato('000000000', $pago->id) . $this->formatoDato('000000', $pago->consecutivo);

            $datosMultipagos['mp_node'] = $plantel->cve_multipagos; //VAlor depente del plantel por ahora default
            $datosMultipagos['mp_concept'] = 1; //Valor depende del caja_conceptos por ahora default

            $datosMultipagos['mp_amount'] = number_format((float) $pago->monto, 2, '.', '');

            $datosMultipagos['mp_customername'] = substr($datos['pagador'], 0, 50);
            $datosMultipagos['mp_currency'] = 1;
            $cadenaCifrar = $datosMultipagos['mp_order'] . $datosMultipagos['mp_reference'] . $datosMultipagos['mp_amount'];
            $parametros = Param::where('llave', 'cifrado_multipagos')->first();
            $datosMultipagos['mp_signature'] = hash_hmac('sha256', $cadenaCifrar, $parametros->valor);
            $parametros = Param::where('llave', 'url_success_multipagos')->first();
            $datosMultipagos['mp_urlsuccess'] = url($parametros->valor);
            $parametros = Param::where('llave', 'url_fail_multipagos')->first();
            $datosMultipagos['mp_urlfailure'] = url($parametros->valor);
            $datosMultipagos['usu_alta_id'] = 1;
            $datosMultipagos['usu_mod_id'] = 1;
            $parametros = Param::where('llave', 'url_multipagos')->first();
            $datosMultipagos['url_peticion'] = $parametros->valor;
            $datosMultipagos['mp_paymentmethod'] = $pago->formaPago->cve_multipagos;
            $datosMultipagos['mp_datereference'] = $adeudo_pago_online->fecha_limite->toDateString();
            $datosMultipagos['navegador'] = $this->getBrowser($_SERVER['HTTP_USER_AGENT']);
            $datosMultipagos['dispositivo'] = $this->getDispositivo();

            //dd($datosMultipagos);
            $peticion_multipagos = PeticionMultipago::create($datosMultipagos);

            //Se actualizan los datos en el registro de pagos en linea
            $adeudo_pago_online->peticion_multipago_id = $peticion_multipagos->id;
            $adeudo_pago_online->save();
        } else {
            //Se actualizan datos multipago cuando repite peticion
            $peticion_multipagos = $adeudo_pago_online->peticionMultipago;
            $peticion_multipagos->contador_peticiones++;
            $peticion_multipagos->save();


            $parametros = Param::where('llave', 'mp_account')->first();
            $datosMultipagos['mp_account'] = $parametros->valor;
            $datosMultipagos['mp_product'] = $cajaLn->cajaConcepto->cve_multipagos;
            $datosMultipagos['mp_order'] = $this->formatoDato('000', $caja->plantel_id) . $this->formatoDato('000000000', $caja->id) . $this->formatoDato('000000', $caja->consecutivo);
            $datosMultipagos['mp_reference'] = $this->formatoDato('000', $caja->plantel_id) . $this->formatoDato('000000000', $pago->id) . $this->formatoDato('000000', $pago->consecutivo);

            $datosMultipagos['mp_node'] = $plantel->cve_multipagos; //VAlor depente del plantel por ahora default
            $datosMultipagos['mp_concept'] = 1; //Valor depende del caja_conceptos por ahora default

            $datosMultipagos['mp_amount'] = number_format((float) $pago->monto, 2, '.', '');
            //$cliNombre = $caja->cliente->nombre . " " . $caja->cliente->nombre2 . " " . $caja->cliente->ape_paterno . " " . $caja->cliente->ape_materno;
            $datosMultipagos['mp_customername'] = substr($datos['pagador'], 0, 50);
            $datosMultipagos['mp_currency'] = 1;
            $cadenaCifrar = $datosMultipagos['mp_order'] . $datosMultipagos['mp_reference'] . $datosMultipagos['mp_amount'];
            $parametros = Param::where('llave', 'cifrado_multipagos')->first();
            $datosMultipagos['mp_signature'] = hash_hmac('sha256', $cadenaCifrar, $parametros->valor);
            $parametros = Param::where('llave', 'url_success_multipagos')->first();
            $datosMultipagos['mp_urlsuccess'] = url($parametros->valor);
            $parametros = Param::where('llave', 'url_fail_multipagos')->first();
            $datosMultipagos['mp_urlfailure'] = url($parametros->valor);
            $datosMultipagos['usu_alta_id'] = 1;
            $datosMultipagos['usu_mod_id'] = 1;
            $parametros = Param::where('llave', 'url_multipagos')->first();
            $datosMultipagos['url_peticion'] = $parametros->valor;
            $datosMultipagos['mp_paymentmethod'] = $pago->formaPago->cve_multipagos;
            //dd($adeudo_pago_online);
            $datosMultipagos['mp_datereference'] = $adeudo_pago_online->fecha_limite->toDateString();
            $datosMultipagos['navegador'] = $this->getBrowser($_SERVER['HTTP_USER_AGENT']);
            $datosMultipagos['dispositivo'] = $this->getDispositivo();

            $peticion_multipagos->update($datosMultipagos);
            //dd($peticion_multipagos);
        }

        return response()->json([
            'datos' => $datosMultipagos,
        ], 200);
        */
        if ($adeudo_pago_online->peticion_multipago_id == 0 or is_null($adeudo_pago_online->peticion_multipago_id)) {
            $datosOpenpay = array();
            $datosOpenpay['pago_id'] = $pago->id;
            $datosOpenpay['cliente_id'] = $caja->cliente_id;
            $datosOpenpay['pname'] = $datos['name'];
            $datosOpenpay['plast_name'] = $datos['last_name'];
            $datosOpenpay['pphone_number'] = $datos['phone_number'];
            $datosOpenpay['pemail'] = $datos['email'];
            $datosOpenpay['pmethod'] = $pago->formaPago->cve_multipagos;
            $datosOpenpay['pamount'] = number_format((float) $pago->monto, 2, '.', '');
            $datosOpenpay['pdescription'] = $cajaLn->cajaConcepto->name;
            $datosOpenpay['p_send_mail'] = false;
            $datosOpenpay['pconfirm'] = false;
            $datosOpenpay['predirect_url'] = route('fichaAdeudos.index');
            //$datosOpenpay['ppreferencia']=;
            $datosOpenpay['porder_id'] = $this->formatoDato('000', $caja->plantel_id) . $this->formatoDato('000000000', $caja->id) . $this->formatoDato('000000', $caja->consecutivo);
            $datosOpenpay['fecha_limite'] = $adeudo_pago_online->fecha_limite->toDateString();
            $datosOpenpay['usu_alta_id'] = Auth::user()->id;
            $datosOpenpay['usu_mod_id'] = Auth::user()->id;

            $peticionOpenpay = PeticionOpenpay::create($datosOpenpay);

            if ($peticionOpenpay->pmethod == 'card') {
                $respuesta = $this->pagoTarjeta($peticionOpenpay, $plantel);
                return response()->json($respuesta);
            } elseif ($peticionOpenpay->pmethod == 'bank_account') {
                $respuesta = $this->pagoBancoNuevo($peticionOpenpay, $plantel);
                return response()->json($respuesta);
            } elseif ($peticionOpenpay->pmethod == 'store') {
                $this->pagoTienda();
            }
        } else {
        }
    }

    public function pagoTarjeta($peticionOpenpay, $plantel)
    {
        try {
            // create instance OpenPay
            $ip = Param::where('llave', 'ip_localhost')->first();
            $openpay = Openpay::getInstance($plantel->oid, $plantel->oprivada, 'MX', $ip->valor);
            $openpay_productivo = Param::where('llave', 'openpay_productivo')->first();

            if ($openpay_productivo->valor == 1) {
                //$openpay->setProductionMode(true);
                Openpay::setProductionMode(true);
            } else {
                //$openpay->setProductionMode(false);
                Openpay::setProductionMode(false);
            }


            // create object customer
            $customer = array(
                'name' => $peticionOpenpay->pname,
                'last_name' => $peticionOpenpay->plast_name,
                'email' => $peticionOpenpay->pemail,
                'phone_number' => $peticionOpenpay->pphone_number,
            );

            // create object charge
            $chargeRequest =  array(
                'method' => $peticionOpenpay->pmethod,
                'amount' => $peticionOpenpay->pamount,
                'description' => $peticionOpenpay->pdescription,
                'customer' => $customer,
                'send_email' => $peticionOpenpay->psend_mail,
                'confirm' => $peticionOpenpay->pconfirm,
                'redirect_url' => $peticionOpenpay->predirect_url,
                'order_id' => $peticionOpenpay->porder_id,
            );
            $charge = $openpay->charges->create($chargeRequest);
            //dd($charge);
            //dd($charge->serializableData['conciliated']);
            //dd($charge->serializableData);
            $peticionOpenpay->rid = $charge->id;
            $peticionOpenpay->rauthorization = $charge->authorization;
            $peticionOpenpay->rmethod = $charge->method;
            $peticionOpenpay->roperation_type = $charge->operation_type;
            $peticionOpenpay->rtransaction_type = $charge->transaction_type;
            $peticionOpenpay->rstatus = $charge->status;
            $peticionOpenpay->rconciliated = $charge->conciliated;
            $peticionOpenpay->rcreation_date = Carbon::parse($charge->creation_date)->format('Y-m-d H:i:s');
            $peticionOpenpay->roperation_date = Carbon::parse($charge->operation_date)->format('Y-m-d H:i:s');
            $peticionOpenpay->rdescription = $charge->description;
            $peticionOpenpay->rerror_message = $charge->error_message;
            $peticionOpenpay->ramount = $charge->amount;
            $peticionOpenpay->rcurrency = $charge->currency;
            $peticionOpenpay->rpayment_method_type = $charge->payment_method->type;
            $peticionOpenpay->rpayment_method_url = $charge->payment_method->url;
            $peticionOpenpay->rorder_id = $charge->order_id;
            //$peticionOpenpay->rcustomer=json_encode($charge->customer);
            $peticionOpenpay->save();
            //dd($peticionOpenpay);
            return array(
                "method" => $peticionOpenpay->pmethod,
                'url' => $peticionOpenpay->rpayment_method_url
            );
        } catch (OpenpayApiTransactionError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (OpenpayApiRequestError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (OpenpayApiConnectionError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (OpenpayApiAuthError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (OpenpayApiError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'error_code' => $e->getCode(),
                    'description' => $e->getMessage()
                ]
            ]);
        }
    }

    public function pagoBancoNuevo($peticionOpenpay, $plantel)
    {

        try {
            // create instance OpenPay
            //dd($peticionOpenpay);
            $ip = Param::where('llave', 'ip_localhost')->first();
            $openpay = Openpay::getInstance($plantel->oid, $plantel->oprivada, 'MX', $ip->valor);
            //dd($openpay);
            $openpay_productivo = Param::where('llave', 'openpay_productivo')->first();

            $url_open_pay = "";
            if ($openpay_productivo->valor == 1) {
                //$openpay->setProductionMode(true);
                Openpay::setProductionMode(true);
                $url_open_pay = Param::where('llave', 'url_openpay_productivo')->value('valor');
            } else {
                //$openpay->setProductionMode(false);
                Openpay::setProductionMode(false);
                $url_open_pay = Param::where('llave', 'url_openpay_sandbox')->value('valor');
            }


            // create object customer
            $customer = array(
                'name' => $peticionOpenpay->pname,
                'last_name' => $peticionOpenpay->plast_name,
                'email' => $peticionOpenpay->pemail,
                'phone_number' => $peticionOpenpay->pphone_number,
                'order_id' => $peticionOpenpay->porder_id,
            );

            // create object charge
            $chargeData  =  array(
                'method' => $peticionOpenpay->pmethod,
                'amount' => $peticionOpenpay->pamount,
                'description' => $peticionOpenpay->pdescription,
                'order_id' => $peticionOpenpay->porder_id,
                'customer' => $customer,
                'due_date' => Carbon::createFromFormat('Y-m-d H:s:i',$peticionOpenpay->fecha_limite)->toISOString()
                //'send_email' => $peticionOpenpay->psend_mail,
                //'confirm' => $peticionOpenpay->pconfirm,
                //'redirect_url' => $peticionOpenpay->predirect_url
            );
            //dd($chargeData);
            $charge = $openpay->charges->create($chargeData);
            //dd($charge);
            //dd($charge->serializableData['conciliated']);
            //dd($charge->serializableData);
            $peticionOpenpay->rid = $charge->id;
            $peticionOpenpay->rauthorization = $charge->authorization;
            $peticionOpenpay->rmethod = $charge->method;
            $peticionOpenpay->roperation_type = $charge->operation_type;
            $peticionOpenpay->rtransaction_type = $charge->transaction_type;
            $peticionOpenpay->rstatus = $charge->status;
            $peticionOpenpay->rconciliated = $charge->conciliated;
            $peticionOpenpay->rcreation_date = Carbon::parse($charge->creation_date)->format('Y-m-d H:i:s');
            //$peticionOpenpay->roperation_date=Carbon::parse($charge->operation_date)->format('Y-m-d H:i:s');
            $peticionOpenpay->rdescription = $charge->description;
            $peticionOpenpay->rerror_message = $charge->error_message;
            $peticionOpenpay->ramount = $charge->amount;
            $peticionOpenpay->rcurrency = $charge->currency;
            $peticionOpenpay->rpayment_method_type = $charge->payment_method->type;
            //$peticionOpenpay->rpayment_method_url=$charge->payment_method->url;
            $peticionOpenpay->rpayment_method_bank = $charge->payment_method->bank;
            $peticionOpenpay->rpayment_method_agreement = $charge->payment_method->agreement;
            $peticionOpenpay->rpayment_method_clabe = $charge->payment_method->clabe;
            $peticionOpenpay->rpayment_method_name = $charge->payment_method->name;
            $peticionOpenpay->rorder_id = $charge->order_id;
            //$peticionOpenpay->rcustomer=json_encode($charge->customer);
            $peticionOpenpay->save();
            return array(
                "method" => $peticionOpenpay->pmethod,
                'url' => $url_open_pay . "/spei-pdf/" . $plantel->oid . "/" . $peticionOpenpay->rid
            );
        } catch (OpenpayApiTransactionError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (OpenpayApiRequestError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (OpenpayApiConnectionError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (OpenpayApiAuthError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (OpenpayApiError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'error_code' => $e->getCode(),
                    'description' => $e->getMessage()
                ]
            ]);
        }
    }

    public function pagoBancoExistente($peticionExistente, $plantel)
    {
        //dd($peticionExistente);
        try {
            // create instance OpenPay

            $openpay_productivo = Param::where('llave', 'openpay_productivo')->first();

            $url_open_pay = "";
            if ($openpay_productivo->valor == 1) {
                $url_open_pay = Param::where('llave', 'url_openpay_productivo')->value('valor');
            } else {
                $url_open_pay = Param::where('llave', 'url_openpay_sandbox')->value('valor');
            }

            $rid = $peticionExistente->rid;
            //dd($rid);
            if (is_null($peticionExistente->rid)) {
                $rid = $this->buscarOpenpayBanco($peticionExistente, $plantel);
                //dd($rid);
            }

            return array(
                "method" => $peticionExistente->pmethod,
                'url' => $url_open_pay . "/spei-pdf/" . $plantel->oid . "/" . $rid
            );
        } catch (Exception $e) {

            return response()->json([
                'error' => [
                    'error_code' => $e->getCode(),
                    'description' => $e->getMessage()
                ]
            ]);
        }
    }

    public function buscarOpenpayBanco($peticion, $plantel)
    {
        $ip = Param::where('llave', 'ip_localhost')->first();
        $openpay = Openpay::getInstance($plantel->oid, $plantel->oprivada, 'MX', $ip->valor);
        //dd($openpay);
        $openpay_productivo = Param::where('llave', 'openpay_productivo')->first();

        $url_open_pay = "";
        if ($openpay_productivo->valor == 1) {
            //$openpay->setProductionMode(true);
            Openpay::setProductionMode(true);
            $url_open_pay = Param::where('llave', 'url_openpay_productivo')->value('valor');
        } else {
            //$openpay->setProductionMode(false);
            Openpay::setProductionMode(false);
            $url_open_pay = Param::where('llave', 'url_openpay_sandbox')->value('valor');
        }

        $findDataRequest = array(
            'order_id' => $peticion->porder_id
        );
        //dd($findDataRequest);

        $chargeList = $openpay->charges->getList($findDataRequest);
        $peticion->rid=$chargeList[0]->id;
        $peticion->save();
        return $peticion->rid;
    }

    public function pagoTiendas()
    {
    }

    public function getCuentasPlantelFormaPago($forma_pago, $plantel)
    {
        $plantel = $plantel;
        $forma_pago = $forma_pago;

        $final = array();
        if ($forma_pago == 1) {
            $r = DB::table('cuentas_efectivos as ce')
                ->select('ce.id', 'ce.name')
                ->join('cuentas_efectivo_plantels as cep', 'cep.cuentas_efectivo_id', '=', 'ce.id')
                ->where('cep.plantel_id', '=', $plantel)
                ->where('ce.bnd_banco', 0)
                ->where('ce.i', '>', '0')
                ->first();
            //dd($r);
            return $r->id;
        } else {
            $r = DB::table('cuentas_efectivos as ce')
                ->select('ce.id', 'ce.name')
                ->join('cuentas_efectivo_plantels as cep', 'cep.cuentas_efectivo_id', '=', 'ce.id')
                ->where('cep.plantel_id', '=', $plantel)
                ->where('ce.bnd_banco', 1)
                ->where('ce.id', '>', '0')
                ->first();
            //dd($r);
            return $r->id;
        }
    }

    public function formatoDato($cadena0, $dato)
    {
        return substr($cadena0, 1, (strlen($cadena0) - strlen($dato))) . $dato;
    }

    public function successMultipagos(Request $request)
    {

        $param = Param::where('llave', 'servidor_respuesta_multipagos')->first();

        //if ($dominio == $param->valor) {
        $datos = $request->all();
        //dd($datos);

        $crearRegistro = array();
        $crearRegistro['mp_order'] = $datos['mp_order'];
        $crearRegistro['mp_reference'] = $datos['mp_reference'];
        $crearRegistro['mp_amount'] = $datos['mp_amount'];
        $crearRegistro['mp_response'] = $datos['mp_response'];
        $crearRegistro['mp_responsemsg'] = $datos['mp_responsemsg'];
        $crearRegistro['mp_authorization'] = $datos['mp_authorization'];
        $crearRegistro['mp_signature'] = $datos['mp_signature'];
        //$crearRegistro['mp_paymentmethod'] = $datos['mp_paymentmethod'];
        $crearRegistro['usu_alta_id'] = 1;
        $crearRegistro['usu_mod_id'] = 1;

        $parametros = Param::where('llave', 'cifrado_multipagos')->first();
        $cadenaCifrar = $crearRegistro['mp_order'] . $crearRegistro['mp_reference'] . $crearRegistro['mp_amount'] . $crearRegistro['mp_authorization'];
        $nuevaFirma = hash_hmac('sha256', $cadenaCifrar, $parametros->valor);
        //dd($cadenaCifrar." - ".$nuevaFirma);
        if ($nuevaFirma == $crearRegistro['mp_signature']) {
            $buscarRegistro = successMultipago::where('mp_order', $crearRegistro['mp_order'])
                ->where('mp_reference', $crearRegistro['mp_reference'])
                ->where('mp_amount', $crearRegistro['mp_amount'])
                ->where('mp_signature', $crearRegistro['mp_signature'])
                ->first();
            if (is_null($buscarRegistro)) {
                try {
                    $success = SuccessMultipago::create($crearRegistro);
                    if (!is_null($success)) {
                        $peticion = PeticionMultipago::where('mp_order', $crearRegistro['mp_order'])
                            ->where('mp_reference', $crearRegistro['mp_reference'])
                            ->where('mp_amount', $crearRegistro['mp_amount'])
                            ->first();
                        $pago = Pago::find($peticion->pago_id);
                        $caja = Caja::find($pago->caja_id);
                        $cajaLn = CajaLn::where('caja_id', $caja->id)->first();
                        $adeudo = Adeudo::where('id', $cajaLn->adeudo_id)->first();

                        //dd($peticion->toArray());
                        if ($datos['mp_response'] == '00' or $datos['mp_response'] == '0' or $datos['mp_response'] == '000') {
                            //$pago = Pago::find($peticion->pago_id);
                            $pago->bnd_pagado = 1;
                            $pago->save();
                            $caja = $pago->caja;
                            $caja->st_caja_id = 1;
                            $caja->save();
                            $adeudo->pagado_bnd = 1;
                            $adeudo->save();

                            //Generar consecutivo pago simplificado
                            $plantel = Plantel::find($caja->plantel_id);
                            $pago_final = Pago::where('caja_id', '=', $caja->id)->orderBy('id', 'desc')->first();
                            $pagos = Pago::where('caja_id', '=', $caja->id)->orderBy('id', 'desc')->get();

                            $mes = Carbon::createFromFormat('Y-m-d', $pago_final->fecha)->month;
                            $anio = Carbon::createFromFormat('Y-m-d', $pago_final->fecha)->year;

                            if ($caja->cajaLn->cajaConcepto->bnd_mensualidad == 1 and is_null($pago_final->csc_simplificado)) {
                                $serie_folio_simplificado = SerieFolioSimplificado::where('cuenta_p_id', $plantel->cuenta_p_id)
                                    ->where('anio', $anio)
                                    ->where('mese_id', 13)
                                    ->where('bnd_activo', 1)
                                    ->where('bnd_fiscal', 1)
                                    ->first();
                                $serie_folio_simplificado->folio_actual = $serie_folio_simplificado->folio_actual + 1;
                                $folio_actual = $serie_folio_simplificado->folio_actual;
                                $serie = $serie_folio_simplificado->serie;
                                $serie_folio_simplificado->save();

                                $relleno = "0000";
                                $consecutivo = substr($relleno, 0, 4 - strlen($folio_actual)) . $folio_actual;
                                foreach ($pagos as $pago) {
                                    $pago->csc_simplificado = $serie . "-" . $consecutivo;
                                    $pago->save();
                                }
                            } elseif ($caja->cajaLn->cajaConcepto->bnd_mensualidad == 0 and is_null($pago_final->csc_simplificado)) {
                                $serie_folio_simplificado = SerieFolioSimplificado::where('cuenta_p_id', $plantel->cuenta_p_id)
                                    ->where('anio', $anio)
                                    ->where('mese_id', $mes)
                                    ->where('bnd_activo', 1)
                                    ->where('bnd_fiscal', 0)
                                    ->first();
                                $serie_folio_simplificado->folio_actual = $serie_folio_simplificado->folio_actual + 1;
                                $serie_folio_simplificado->save();
                                $folio_actual = $serie_folio_simplificado->folio_actual;
                                $mes_prefijo = $serie_folio_simplificado->mes1->abreviatura;
                                $anio_prefijo = $anio - 2000;
                                $serie = $serie_folio_simplificado->serie;


                                $relleno = "0000";
                                $consecutivo = substr($relleno, 0, 4 - strlen($folio_actual)) . $folio_actual;
                                foreach ($pagos as $pago) {
                                    $pago->csc_simplificado = $serie . "-" . $mes_prefijo . $anio_prefijo . "-" . $consecutivo;
                                    $pago->save();
                                }
                            }
                            //Fin crear consecutivo simplificado
                        }
                    }
                } catch (Exception $e) {
                    dd($e);
                    Log::info($e->getMessage);
                }
            }
            return redirect()->route('fichaAdeudos.index');
        } else {
            Log::info($datos['mp_order'] . "-" . $datos['mp_reference'] . "-" . $datos['mp_amount'] . "- Firma Incorrecta");
            dd('Firma incorrecta');
        }
    }

    public function successOpenpay($id)
    {
        try {
            //$success = SuccessMultipago::create($crearRegistro);
            $peticion = PeticionOpenpay::where('rid', $id)->first();
            if (!is_null($peticion)) {
                $peticion->bnd_pagado = 1;
                $peticion->notificacion_pagado = date('Y-m-d H:i:s');
                $peticion->save();
                $pago = Pago::find($peticion->pago_id);
                $caja = Caja::find($pago->caja_id);
                $cajaLn = CajaLn::where('caja_id', $caja->id)->first();
                $adeudo = Adeudo::where('id', $cajaLn->adeudo_id)->first();

                //dd($peticion->toArray());


                $pago->bnd_pagado = 1;
                $pago->save();
                $caja = $pago->caja;
                $caja->st_caja_id = 1;
                $caja->save();
                $adeudo->pagado_bnd = 1;
                $adeudo->save();

                //Generar consecutivo pago simplificado
                $plantel = Plantel::find($caja->plantel_id);
                $pago_final = Pago::where('caja_id', '=', $caja->id)->orderBy('id', 'desc')->first();
                $pagos = Pago::where('caja_id', '=', $caja->id)->orderBy('id', 'desc')->get();

                $mes = Carbon::createFromFormat('Y-m-d', $pago_final->fecha)->month;
                $anio = Carbon::createFromFormat('Y-m-d', $pago_final->fecha)->year;

                if ($caja->cajaLn->cajaConcepto->bnd_mensualidad == 1 and is_null($pago_final->csc_simplificado)) {
                    $serie_folio_simplificado = SerieFolioSimplificado::where('cuenta_p_id', $plantel->cuenta_p_id)
                        ->where('anio', $anio)
                        ->where('mese_id', 13)
                        ->where('bnd_activo', 1)
                        ->where('bnd_fiscal', 1)
                        ->first();
                    $serie_folio_simplificado->folio_actual = $serie_folio_simplificado->folio_actual + 1;
                    $folio_actual = $serie_folio_simplificado->folio_actual;
                    $serie = $serie_folio_simplificado->serie;
                    $serie_folio_simplificado->save();

                    $relleno = "0000";
                    $consecutivo = substr($relleno, 0, 4 - strlen($folio_actual)) . $folio_actual;
                    foreach ($pagos as $pago) {
                        $pago->csc_simplificado = $serie . "-" . $consecutivo;
                        $pago->save();
                    }
                } elseif ($caja->cajaLn->cajaConcepto->bnd_mensualidad == 0 and is_null($pago_final->csc_simplificado)) {
                    $serie_folio_simplificado = SerieFolioSimplificado::where('cuenta_p_id', $plantel->cuenta_p_id)
                        ->where('anio', $anio)
                        ->where('mese_id', $mes)
                        ->where('bnd_activo', 1)
                        ->where('bnd_fiscal', 0)
                        ->first();
                    $serie_folio_simplificado->folio_actual = $serie_folio_simplificado->folio_actual + 1;
                    $serie_folio_simplificado->save();
                    $folio_actual = $serie_folio_simplificado->folio_actual;
                    $mes_prefijo = $serie_folio_simplificado->mes1->abreviatura;
                    $anio_prefijo = $anio - 2000;
                    $serie = $serie_folio_simplificado->serie;


                    $relleno = "0000";
                    $consecutivo = substr($relleno, 0, 4 - strlen($folio_actual)) . $folio_actual;
                    foreach ($pagos as $pago) {
                        $pago->csc_simplificado = $serie . "-" . $mes_prefijo . $anio_prefijo . "-" . $consecutivo;
                        $pago->save();
                    }
                }
                //Fin crear consecutivo simplificado

            }
        } catch (Exception $e) {
            dd($e);
            Log::info($e->getMessage());
        }
        return redirect()->route('fichaAdeudos.index');
    }

    public function imprimir(Request $request)
    {
        $data = $request->all();

        $pago = Pago::find($data['pago']);

        $caja = Caja::find($pago->caja_id);

        $input['caja_id'] = $caja->id;
        $input['pago_id'] = $pago->id;
        $input['cliente_id'] = $caja->cliente_id;
        $input['plantel_id'] = $caja->plantel_id;
        $input['consecutivo'] = $caja->consecutivo;
        $input['monto'] = $pago->monto;
        $input['toke_unico'] = uniqid(base64_encode(Str::random(6)));
        $input['usu_alta_id'] = 1;
        $input['usu_mod_id'] = 1;
        $input['fecha_pago'] = $pago->fecha;
        $impresion_token = ImpresionTicket::create($input);

        $acumulado = Pago::select('monto')->where('caja_id', '=', $caja->id)->sum('monto');

        $adeudo = Adeudo::where('caja_id', '=', $caja->id)->first();

        if (!is_null($adeudo)) {
            $combinacion = CombinacionCliente::find($adeudo->combinacion_cliente_id);
            //dd($combinacion);
            $cliente = Cliente::find($caja->cliente_id);
            $empleado = Empleado::where('user_id', '=', Auth::user()->id)->first();

            $carbon = new \Carbon\Carbon();
            $date = $carbon->now();
            $date = $date->format('d-m-Y h:i:s');

            //dd($adeudo->toArray());
            /*return view('cajas.imprimirTicketPago', array(
                'cliente' => $cliente,
                'caja' => $caja,
                'empleado' => $empleado,
                'fecha' => $date,
                'combinacion' => $combinacion,
                'pago' => $pago,
                'acumulado' => $acumulado
            ));*/
        } else {
            $combinacion = 0;
            $cliente = Cliente::find($caja->cliente_id);
            $empleado = Empleado::where('user_id', '=', $pago->usua_alta_id)->first();

            $carbon = new \Carbon\Carbon();
            $date = $carbon->now();
            $date = $date->format('d-m-Y h:i:s');

            //dd($adeudo->toArray());

        }


        $formatter = new NumeroALetras;
        $totalEntero = intdiv($caja->total, 1);
        $centavos = ($caja->total - $totalEntero) * 100;
        $totalLetra = $formatter->toMoney($totalEntero, 2, "Pesos", 'Centavos');
        //dd($centavos);

        //dd($fechaLetra);


        return view('fichaPagos.imprimirTicketPago', array(
            'cliente' => $cliente,
            'caja' => $caja,
            'empleado' => $empleado,
            'fecha' => $date,
            'combinacion' => $combinacion,
            'pago' => $pago,
            'acumulado' => $acumulado,
            'impresion_token' => $impresion_token,
            'totalLetra' => $totalLetra,
            'centavos' => $centavos,
            //'fechaLetra' => $fechaLetra
        ));
    }

    public function datosFactura(Request $request)
    {
        $datos = $request->all();

        $adeudoPagoOnLine = AdeudoPagoOnLine::find($datos['pagoOnLine']);
        $cliente = $adeudoPagoOnLine->cliente;
        $tipoPersonas = TipoPersona::pluck('name', 'id');
        $adeudo_pago_on_line = $adeudoPagoOnLine->id;
        $usoFactura = UsoFactura::select('id', DB::raw('concat(clave,"-",descripcion) as name'))->pluck('name', 'id');
        $regimenFiscal = RegimenFiscal::select('id', DB::raw('concat(clave,"-",descripcion) as name'))->pluck('name', 'id');
        return view('fichaPagos.datos_factura', compact('cliente', 'tipoPersonas', 'adeudo_pago_on_line', 'usoFactura', 'regimenFiscal'));
    }

    public function confirmarFactura(Request $request, $id)
    {

        $datos = $request->except('adeudo_pago_on_line');
        //dd($datos);
        $rules = [
            'tipo_persona_id' => 'required',
            'frazon' => 'required',
            'frfc' => 'required',
            'fcalle' => 'required',
            'fno_exterior' => 'required',
            'fcolonia' => 'required',
            'festado' => 'required',
            'fpais' => 'required',
            'fcp' => 'required',
            'curp' => 'required',
            'fmail' => 'required',
            'regimen_fiscal_id' => 'required'
        ];
        $customMessages = [
            'required' => 'El campo es obligatorio, capturar un valor.'
        ];
        $request->validate($rules, $customMessages);
        //dd($v);
        $adeudoPagoOnLine = AdeudoPagoOnLine::find($id);
        $adeudo = $adeudoPagoOnLine->adeudo;
        $nivelEducativoSat = NivelEducativoSat::find($adeudo->combinacionCliente->grado->nivel_educativo_sat_id);
        $cliente = $adeudoPagoOnLine->cliente;
        $cliente->update($datos);
        $plantel = $adeudoPagoOnLine->cliente->plantel;
        $pago = $adeudoPagoOnLine->pago;
        $caja = $adeudoPagoOnLine->caja;
        //dd($caja->toArray());
        $fecha_anio = Carbon::createFromFormat('Y-m-d', $adeudo->fecha_pago)->year;
        //Parametros para el webservice
        $url = Param::where('llave', 'webServiceFacturacion')->first();
        //$cuenta = Param::where('llave', 'cuentaFacturacion')->first();
        //$password = Param::where('llave', 'passwordFacturacion')->first();
        //$usuario = Param::where('llave', 'usuarioFacturacion')->first();

        try {
            $opts = array(
                'http' => array(
                    'user_agent' => 'PHPSoapClient'
                )
            );
            $context = stream_context_create($opts);

            $wsdlUrl = $url->valor;
            $soapClientOptions = array(
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE
            );

            $client = new SoapClient($wsdlUrl, $soapClientOptions);

            //dd($client->__getFunctions());
            $fecha_solicitud_factura_tabla = date('Y-m-d H:i:s');
            $fecha_solicitud_factura_service = date('Y-m-d\TH:i:s');

            $pagos = Pago::where('caja_id', $adeudo->caja_id)->get();

            //dd($pagos->toArray());
            $total_pagos = 0;
            foreach ($pagos as $pago) {
                $total_pagos = $total_pagos + $pago->monto;
            }
            //dd($cliente->usoFactura);
            $grado = $adeudo->combinacionCliente->grado;
            if (
                is_null($grado->nivel_educativo_sat_id) or $grado->nivel_educativo_sat_id == "" or
                is_null($grado->clave_servicio) or $grado->clave_servicio == "" or
                is_null($grado->seccion) or $grado->seccion == "" or
                is_null($grado->fec_rvoe) or $grado->fec_rvoe == "" or
                is_null($grado->rvoe) or $grado->rvoe == ""
            ) {
                dd("Uno o más datos no estan definidos en el grado con id:" . $grado->id);
            }

            $objetosArray = array();

            if ($adeudo->combinacionCliente->grado->clave_servicio == "86121600") {
                $objetosArray = array(
                    'credenciales' => array(
                        'Cuenta' => $plantel->fcuenta,
                        'Password' => $plantel->fpassword,
                        'Usuario' => $plantel->fusuario
                    ),
                    'cfdi' => array(
                        'Addenda' => array(
                            /*'DomicilioEmisor' => array(
                                'Calle' => $plantel->matriz->calle,
                                'CodigoPostal' => $plantel->matriz->cp,
                                'Colonia' => $plantel->matriz->colonia,
                                'Estado' => $plantel->matriz->estado,
                                //'Localidad' => $cliente->flocalidad,
                                'Municipio' => $plantel->matriz->municipio,
                                'NombreCliente' => $plantel->matriz->nombre_corto,
                                'NumeroExterior' => $plantel->matriz->no_ext,
                                'NumeroInterior' => $plantel->matriz->no_int,
                                'Pais' => 'Mexico',
                                //'Referencia'=>$cliente->,
                                //'Telefono'=>
                            ),*/
                            'DomicilioEmisor' => array(
                                'Calle' => $plantel->calle,
                                'CodigoPostal' => $plantel->cp,
                                'Colonia' => $plantel->colonia,
                                'Estado' => $plantel->estado,
                                //'Localidad' => $cliente->flocalidad,
                                'Municipio' => $plantel->municipio,
                                'NombreCliente' => $plantel->nombre_corto,
                                'NumeroExterior' => $plantel->no_ext,
                                'NumeroInterior' => $plantel->no_int,
                                'Pais' => 'Mexico',
                                //'Referencia'=>$cliente->,
                                //'Telefono'=>
                            ),
                            'DomicilioReceptor' => array(
                                'Calle' => $cliente->fcalle,
                                'CodigoPostal' => $cliente->fcp,
                                'Colonia' => $cliente->fcolonia,
                                'Estado' => $cliente->festado,
                                'Localidad' => $cliente->flocalidad,
                                'Municipio' => $cliente->fmunicipio,
                                'NombreCliente' => $cliente->fno_interior,
                                'NumeroExterior' => $cliente->fno_exterior,
                                'NumeroInterior' => $cliente->fno_interior,
                                'Pais' => $cliente->fpais,
                                //'Referencia'=>$cliente->,
                                //'Telefono'=>
                            )/*,
                            'DomicilioSucursal' => array(
                                'Calle' => $plantel->calle,
                                'CodigoPostal' => $plantel->cp,
                                'Colonia' => $plantel->colonia,
                                'Estado' => $plantel->estado,
                                //'Localidad' => $cliente->flocalidad,
                                'Municipio' => $plantel->municipio,
                                'NombreCliente' => $plantel->nombre_corto,
                                'NumeroExterior' => $plantel->no_ext,
                                'NumeroInterior' => $plantel->no_int,
                                'Pais' => 'Mexico',
                                //'Referencia'=>$cliente->,
                                //'Telefono'=>
                            ),*/
                        ),
                        'ClaveCFDI' => 'FAC', //Requerido valor default para ingresos segun documento tecnico del proveedor
                        //Plantel emisor de factura
                        'Emisor' => array(
                            'Nombre' => $cliente->plantel->nombre_corto,
                            'RegimenFiscal' => $cliente->plantel->regimen_fiscal, //Campo nuevo en planteles
                        ),
                        //Cliente
                        'Receptor' => array(
                            'Nombre' => $cliente->frazon,
                            'Rfc' => $cliente->frfc, //'TEST010203001',
                            'UsoCFDI' => $cliente->usoFactura->clave //$adeudo->cajaConcepto->uso_factura, //campo nuevo en conceptos de caja, Definir valor Default de acuerdo al SAT
                        ),
                        //'CondicionesDePago' => 'CONDICIONES', //opcional
                        'FormaPago' => $pago->formaPago->cve_sat, //No es Opcional Documentacion erronea, llenar en tabla campo nuevo
                        'Fecha' => $fecha_solicitud_factura_service,
                        'MetodoPago' => 'PUE', //No es Opcional Documentacion erronea, Definir default segun catalogo del SAT
                        'LugarExpedicion' => $cliente->plantel->cp, //CP del plantel, debe ser valido segun catalogo del SAT
                        'Moneda' => 'MXN', //Default
                        'Referencia' => $pago->csc_simplificado,  //Definir valor
                        'Conceptos' => array('ConceptoR' => array(
                            'Cantidad' => '1',
                            'ClaveProdServ' => $adeudo->combinacionCliente->grado->clave_servicio, //Definir valor defaul de acuerdo al SAT
                            'ClaveUnidad' => 'E48',
                            'Unidad' => 'Servicio', //Definir valor default
                            'Descripcion' => $caja->cajaLn->cajaConcepto->leyenda_factura . " " . $fecha_anio,
                            'Impuestos' => array('Traslados' => array('TrasladoConceptoR' => array( //no se manejan impuestos
                                'Base' => number_format($total_pagos, 2, '.', ''),
                                //'Importe' => '0.00',
                                'Impuesto' => '002',
                                //'TasaOCuota' => '0.000000',
                                'TipoFactor' => 'Exento'
                            ),),),
                            'InstEducativas' => array(
                                'AutRVOE' => $adeudo->combinacionCliente->grado->rvoe,
                                'CURP' => $cliente->curp,
                                'NivelEducativo' => $nivelEducativoSat->name,
                                'NombreAlumno' => $cliente->nombre . " " . $cliente->nombre2 . " " . $cliente->ape_paterno . " " . $cliente->ape_materno,
                                'RfcPago' => $cliente->frfc
                            ),
                            //'NoIdentificacion' => '00003', //Opcional
                            'Importe' => number_format($total_pagos, 2, '.', ''),
                            'ValorUnitario' => number_format($total_pagos, 2, '.', '')
                        ),),
                        'SubTotal' => number_format($total_pagos, 2, '.', ''),
                        'Total' => number_format($total_pagos, 2, '.', '')
                    )
                );
            } elseif ($adeudo->combinacionCliente->grado->clave_servicio == "86121700") {
                $objetosArray = array(
                    'credenciales' => array(
                        'Cuenta' => $plantel->fcuenta,
                        'Password' => $plantel->fpassword,
                        'Usuario' => $plantel->fusuario
                    ),
                    'cfdi' => array(
                        'Addenda' => array(
                            /*'DomicilioEmisor' => array(
                                'Calle' => $plantel->matriz->calle,
                                'CodigoPostal' => $plantel->matriz->cp,
                                'Colonia' => $plantel->matriz->colonia,
                                'Estado' => $plantel->matriz->estado,
                                //'Localidad' => $cliente->flocalidad,
                                'Municipio' => $plantel->matriz->municipio,
                                'NombreCliente' => $plantel->matriz->nombre_corto,
                                'NumeroExterior' => $plantel->matriz->no_ext,
                                'NumeroInterior' => $plantel->matriz->no_int,
                                'Pais' => 'Mexico',
                                //'Referencia'=>$cliente->,
                                //'Telefono'=>
                            ),*/
                            'DomicilioEmisor' => array(
                                'Calle' => $plantel->calle,
                                'CodigoPostal' => $plantel->cp,
                                'Colonia' => $plantel->colonia,
                                'Estado' => $plantel->estado,
                                //'Localidad' => $cliente->flocalidad,
                                'Municipio' => $plantel->municipio,
                                'NombreCliente' => $plantel->nombre_corto,
                                'NumeroExterior' => $plantel->no_ext,
                                'NumeroInterior' => $plantel->no_int,
                                'Pais' => 'Mexico',
                                //'Referencia'=>$cliente->,
                                //'Telefono'=>
                            ),
                            'DomicilioReceptor' => array(
                                'Calle' => $cliente->fcalle,
                                'CodigoPostal' => $cliente->fcp,
                                'Colonia' => $cliente->fcolonia,
                                'Estado' => $cliente->festado,
                                'Localidad' => $cliente->flocalidad,
                                'Municipio' => $cliente->fmunicipio,
                                'NombreCliente' => $cliente->fno_interior,
                                'NumeroExterior' => $cliente->fno_exterior,
                                'NumeroInterior' => $cliente->fno_interior,
                                'Pais' => $cliente->fpais,
                                //'Referencia'=>$cliente->,
                                //'Telefono'=>
                            )/*,
                            'DomicilioSucursal' => array(
                                'Calle' => $plantel->calle,
                                'CodigoPostal' => $plantel->cp,
                                'Colonia' => $plantel->colonia,
                                'Estado' => $plantel->estado,
                                //'Localidad' => $cliente->flocalidad,
                                'Municipio' => $plantel->municipio,
                                'NombreCliente' => $plantel->nombre_corto,
                                'NumeroExterior' => $plantel->no_ext,
                                'NumeroInterior' => $plantel->no_int,
                                'Pais' => 'Mexico',
                                //'Referencia'=>$cliente->,
                                //'Telefono'=>
                            ),*/
                        ),
                        'ClaveCFDI' => 'FAC', //Requerido valor default para ingresos segun documento tecnico del proveedor
                        //Plantel emisor de factura
                        'Emisor' => array(
                            'Nombre' => $cliente->plantel->nombre_corto,
                            'RegimenFiscal' => $cliente->plantel->regimen_fiscal, //Campo nuevo en planteles
                        ),
                        //Cliente
                        'Receptor' => array(
                            'Nombre' => $cliente->frazon,
                            'Rfc' => $cliente->frfc, //'TEST010203001',
                            'UsoCFDI' => $cliente->usoFactura->clave //$adeudo->cajaConcepto->uso_factura, //campo nuevo en conceptos de caja, Definir valor Default de acuerdo al SAT
                        ),
                        //'CondicionesDePago' => 'CONDICIONES', //opcional
                        'FormaPago' => $pago->formaPago->cve_sat, //No es Opcional Documentacion erronea, llenar en tabla campo nuevo
                        'Fecha' => $fecha_solicitud_factura_service,
                        'MetodoPago' => 'PUE', //No es Opcional Documentacion erronea, Definir default segun catalogo del SAT
                        'LugarExpedicion' => $cliente->plantel->cp, //CP del plantel, debe ser valido segun catalogo del SAT
                        'Moneda' => 'MXN', //Default
                        'Referencia' => $pago->csc_simplificado,  //Definir valor
                        'Conceptos' => array('ConceptoR' => array(
                            'Cantidad' => '1',
                            'ClaveProdServ' => $adeudo->combinacionCliente->grado->clave_servicio, //Definir valor defaul de acuerdo al SAT
                            'ClaveUnidad' => 'E48',
                            'Unidad' => 'Servicio', //Definir valor default
                            'Descripcion' => $cliente->nombre . " " . $cliente->nombre2 . " " . $cliente->ape_paterno . " " . $cliente->ape_materno . PHP_EOL .
                                $caja->cajaLn->cajaConcepto->leyenda_factura . " " . $fecha_anio . PHP_EOL .
                                $adeudo->combinacionCliente->grado->name . PHP_EOL .
                                "CURP: " . $cliente->curp . PHP_EOL .
                                "RVOE: " . $adeudo->combinacionCliente->grado->rvoe,
                            'Impuestos' => array('Traslados' => array('TrasladoConceptoR' => array( //no se manejan impuestos
                                'Base' => number_format($total_pagos, 2, '.', ''),
                                //'Importe' => '0.00',
                                'Impuesto' => '002',
                                //'TasaOCuota' => '0.000000',
                                'TipoFactor' => 'Exento'
                            ),),),
                            /*'InstEducativas' => array(
                                'AutRVOE' => $adeudo->combinacionCliente->grado->rvoe,
                                'CURP' => $cliente->curp,
                                'NivelEducativo' => $nivelEducativoSat->name,
                                'NombreAlumno' => $cliente->nombre . " " . $cliente->nombre2 . " " . $cliente->ape_paterno . " " . $cliente->ape_materno,
                                'RfcPago' => $cliente->frfc
                            ),*/
                            //'NoIdentificacion' => '00003', //Opcional
                            'Importe' => number_format($total_pagos, 2, '.', ''),
                            'ValorUnitario' => number_format($total_pagos, 2, '.', '')
                        ),),
                        'SubTotal' => number_format($total_pagos, 2, '.', ''),
                        'Total' => number_format($total_pagos, 2, '.', '')
                    )
                );
            }
            //Log::info($objetosArray);
            //dd($objetosArray);
            $result = $client->GenerarCFDI($objetosArray)->GenerarCFDIResult;
            //dd($result);
            if (!is_null($result->ErrorDetallado) and $result->ErrorDetallado <> "" and $result->OperacionExitosa <> true) {

                Session::flash('error', $result->ErrorGeneral);

                $message = new Message;
                $message->setAttribute('user_id', Auth::user()->id);
                //$message->setAttribute('code_error', 1);
                $message->setAttribute('mensaje', $result->ErrorDetallado . " - " . $result->ErrorGeneral);
                $message->save();

                Log::info("Mensaje de error api folios digitales facturacion: " . $result->ErrorGeneral);

                /*Notificacion no envia
                $toUser1 = User::find(1);
                $toUser2 = User::find(3);

                // send notification using the "Notification" facade
                Notification::send($toUser1, new NotificacionErrorApiFoliosDigitales($toUser2));
                */

                $destinatario = "linares82@gmail.com";
                $n = Auth::user()->name;
                $asunto = "Problema Folios Digitales";
                $contenido = $message->mensaje . " fecha y hora: " . $message->created_at;
                $from = "ohpelayo@gmail.com";

                //dd(env('MAIL_FROM_ADDRESS'));

                $data = array('contenido' => $contenido, 'nombre' => $n, 'correo' => $from);
                $r = \Mail::send('correos.errorApiFiolsDigitales', $data, function ($message)
                use ($asunto, $destinatario, $n, $from) {
                    $message->from(env('MAIL_FROM_ADDRESS', 'hola@grupocedva.com'), env('MAIL_FROM_NAME', 'Grupo CEDVA'));
                    $message->to($destinatario, $n)->subject($asunto);
                    $message->replyTo($from);
                });

                return back();
            } elseif ($result->OperacionExitosa == true) {
                /*
                $p = xml_parser_create();
                xml_parse_into_struct($p, $result->XML, $vals, $index);
                xml_parser_free($p);
                dd($vals);
                */
                //dd($result);
                $xmlArray = $this->xmlstr_to_array($result->XML);
                //dd($xmlArray["cfdi:Complemento"]["tfd:TimbreFiscalDigital"]["@attributes"]["UUID"]);
                $pagos1 = Pago::where('caja_id', $adeudo->caja_id)->whereNull('deleted_at')->get();
                //dd($pagos->toArray());
                $folio = ++$plantel->folio_facturados;
                $plantel->save();
                foreach ($pagos1 as $pago1) {
                    $pago1->uuid = $xmlArray["cfdi:Complemento"]["tfd:TimbreFiscalDigital"]["@attributes"]["UUID"];
                    $pago1->cbb = $result->CBB;
                    $pago1->xml = $result->XML;
                    $pago1->fecha_solicitud_factura = $fecha_solicitud_factura_tabla;
                    $pago1->serie_factura = $plantel->serie_factura;
                    $pago1->folio_facturados = $folio;

                    $pago1->save();

                    //Envio de correo por parte del proveedor

                    $objetoArray = array(
                        'credenciales' => array(
                            'Cuenta' => $plantel->fcuenta,
                            'Password' => $plantel->fpassword,
                            'Usuario' => $plantel->fusuario
                        ),
                        'uuid' => $pago1->uuid,
                        'email' => $cliente->fmail,
                        'titulo' => "Factura " . $plantel->nombre_corto,
                        'mensaje' => "",
                    );
                    Log::info('uuid:' . $pago1->uuid .
                        ' email:' . $cliente->fmail .
                        ' titulo:' . "Factura " . $plantel->nombre_corto .
                        ' mensaje:' . "");
                    $result = $client->EnviarCFDI($objetoArray)->EnviarCFDIResult;
                    if (!is_null($result->ErrorDetallado) and $result->ErrorDetallado <> "" and $result->OperacionExitosa <> true) {
                        Session::flash('error', $result->ErrorGeneral);
                        return back();
                    }
                }
            }
        } catch (\Exception $e) {
            $destinatario = "linares82@gmail.com";
            $n = Auth::user()->name;
            $asunto = "Problema Folios Digitales error try catch";
            $contenido = $e->getMessage();
            $from = "ohpelayo@gmail.com";

            //dd(env('MAIL_FROM_ADDRESS'));

            $data = array('contenido' => $contenido, 'nombre' => $n, 'correo' => $from);
            $r = \Mail::send('correos.errorApiFiolsDigitales', $data, function ($message)
            use ($asunto, $destinatario, $n, $from) {
                $message->from(env('MAIL_FROM_ADDRESS', 'hola@grupocedva.com'), env('MAIL_FROM_NAME', 'Grupo CEDVA'));
                $message->to($destinatario, $n)->subject($asunto);
                $message->replyTo($from);
            });
            dd($e->getMessage());
        }
        return redirect()->route('fichaAdeudos.index');
        //dd($cliente->toArray());
        //dd($adeudoPagoOnLine);
    }

    function xmlstr_to_array($xmlstr)
    {
        $doc = new DOMDocument();
        $doc->loadXML($xmlstr);
        $root = $doc->documentElement;
        $output = $this->domnode_to_array($root);
        $output['@root'] = $root->tagName;
        return $output;
    }
    function domnode_to_array($node)
    {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->domnode_to_array($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;
                        if (!isset($output[$t])) {
                            $output[$t] = array();
                        }
                        $output[$t][] = $v;
                    } elseif ($v || $v === '0') {
                        $output = (string) $v;
                    }
                }
                if ($node->attributes->length && !is_array($output)) { //Has attributes but isn't an array
                    $output = array('@content' => $output); //Change output into an array.
                }
                if (is_array($output)) {
                    if ($node->attributes->length) {
                        $a = array();
                        foreach ($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string) $attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1 && $t != '@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }
        return $output;
    }

    public function getFacturaXmlByUuid(Request $request)
    {
        $datos = $request->all();
        //Parametros para el webservice
        $url = Param::where('llave', 'webServiceFacturacion')->first();
        //$cuenta = Param::where('llave', 'cuentaFacturacion')->first();
        //$password = Param::where('llave', 'passwordFacturacion')->first();
        //$usuario = Param::where('llave', 'usuarioFacturacion')->first();
        $pago = Pago::where('uuid', $datos['uuid'])->first();
        $plantel = $pago->caja->plantel;

        try {
            $opts = array(
                'http' => array(
                    'user_agent' => 'PHPSoapClient'
                )
            );
            $context = stream_context_create($opts);

            $wsdlUrl = $url->valor;
            $soapClientOptions = array(
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE
            );

            $client = new SoapClient($wsdlUrl, $soapClientOptions);

            //dd($client->__getFunctions());

            $objetosArray = array(
                'credenciales' => array(
                    'Cuenta' => $plantel->fcuenta,
                    'Password' => $plantel->fpassword,
                    'Usuario' => $plantel->fusuario
                ),
                'uuid' => $datos['uuid'],
            );
            //dd($objetosArray);
            $result = $client->ObtenerXMLPorUUID($objetosArray)->ObtenerXMLPorUUIDResult;
            //dd($result);
            if ($result->OperacionExitosa <> true) {
                dd($result->ErrorGeneral);
                Session::flash('error', $result->ErrorGeneral);
                return back();
            } elseif ($result->OperacionExitosa == true) {
                //$xml = simplexml_load_string($result->XML);
                //dd($xml);
                return response()->attachment($result->XML);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }


    public function getFacturaPdfByUuid(Request $request)
    {
        $datos = $request->all();
        //Parametros para el webservice
        $url = Param::where('llave', 'webServiceFacturacion')->first();
        //$cuenta = Param::where('llave', 'cuentaFacturacion')->first();
        //$password = Param::where('llave', 'passwordFacturacion')->first();
        //$usuario = Param::where('llave', 'usuarioFacturacion')->first();
        $pago = Pago::where('uuid', $datos['uuid'])->first();
        $plantel = $pago->caja->plantel;

        try {
            $opts = array(
                'http' => array(
                    'user_agent' => 'PHPSoapClient'
                )
            );
            $context = stream_context_create($opts);

            $wsdlUrl = $url->valor;
            $soapClientOptions = array(
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE
            );

            $client = new SoapClient($wsdlUrl, $soapClientOptions);

            //dd($client->__getFunctions());

            $objetosArray = array(
                'credenciales' => array(
                    'Cuenta' => $plantel->fcuenta,
                    'Password' => $plantel->fpassword,
                    'Usuario' => $plantel->fusuario
                ),
                'uuid' => $datos['uuid'],
                'nombrePlantilla' => ''
            );
            //dd($objetosArray);
            $result = $client->ObtenerPdf($objetosArray)->ObtenerPDFResult;

            if ($result->OperacionExitosa <> true) {
                dd($result->ErrorGeneral);
                Session::flash('error', $result->ErrorGeneral);
                return back();
            } elseif ($result->OperacionExitosa == true) {
                $data = base64_decode($result->PDF);
                header('Content-Type: application/pdf');
                echo $data;
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        //dd($result);
    }

    public function datosFiscales(Request $request)
    {
        $datos = $request->all();

        //$adeudoPagoOnLine = AdeudoPagoOnLine::find($datos['pagoOnLine']);
        $cliente = Cliente::where('matricula', Auth::user()->name)->first();
        //dd($cliente);
        $tipoPersonas = TipoPersona::pluck('name', 'id');
        $usoFactura = UsoFactura::select('id', DB::raw('concat(clave,"-",descripcion) as name'))->pluck('name', 'id');
        $regimenFiscal = RegimenFiscal::select('id', DB::raw('concat(clave,"-",descripcion) as name'))->pluck('name', 'id');
        //$adeudo_pago_on_line = $adeudoPagoOnLine->id;
        return view('fichaPagos.datos_fiscales', compact('cliente', 'tipoPersonas', 'usoFactura', 'regimenFiscal'));
    }

    public function confirmarDatosFiscales(Request $request, $id)
    {
        $datos = $request->except('adeudo_pago_on_line');
        //dd($datos);
        $rules = [
            'tipo_persona_id' => 'required',
            'frazon' => 'required',
            'frfc' => 'required',
            'fcalle' => 'required',
            'fno_exterior' => 'required',
            'fcolonia' => 'required',
            'festado' => 'required',
            'fpais' => 'required',
            'fcp' => 'required',
            'curp' => 'required',
            'fmail' => 'required',
            'regimen_fiscal_id' => 'requiered'
        ];
        $customMessages = [
            'required' => 'El campo es obligatorio, capturar un valor.'
        ];
        //$request->validate($rules, $customMessages);
        //dd($v);
        $adeudoPagoOnLine = AdeudoPagoOnLine::find($id);

        $cliente = Cliente::find($id);
        $cliente->update($datos);

        return redirect()->route('fichaAdeudos.index');
        //dd($cliente->toArray());
        //dd($adeudoPagoOnLine);
    }

    public function cmbUsoFactura(Request $request)
    {
        if ($request->ajax()) {
            //dd($request->all());
            $tipoPersona = $request->get('tipo_persona_id');
            $uso_factura = $request->get('uso_factura_id');

            $final = array();
            $r_aux = DB::table('uso_facturas as uf');
            if ($tipoPersona == 1) {
                $r_aux->select('id', DB::raw('concat(clave,"-",descripcion) as name'))
                    ->where('uf.bnd_fisica', 1)
                    ->whereNull('deleted_at');
            } else {
                $r_aux->select('id', DB::raw('concat(clave,"-",descripcion) as name'))
                    ->where('uf.bnd_moral', 1)
                    ->whereNull('deleted_at');
            }

            $r = $r_aux->get();
            //dd($r);
            if (isset($uso_factura) and $uso_factura != 0) {
                foreach ($r as $r1) {
                    if ($r1->id == $uso_factura) {
                        array_push($final, array(
                            'id' => $r1->id,
                            'name' => $r1->name,
                            'selectec' => 'Selected',
                        ));
                    } else {
                        array_push($final, array(
                            'id' => $r1->id,
                            'name' => $r1->name,
                            'selectec' => '',
                        ));
                    }
                }
                return $final;
            } else {
                return $r;
            }
        }
    }

    function getBrowser($user_agent)
    {

        if (strpos($user_agent, 'MSIE') !== FALSE)
            return 'Internet explorer';
        elseif (strpos($user_agent, 'Edge') !== FALSE) //Microsoft Edge
            return 'Microsoft Edge';
        elseif (strpos($user_agent, 'Trident') !== FALSE) //IE 11
            return 'Internet explorer';
        elseif (strpos($user_agent, 'Opera Mini') !== FALSE)
            return "Opera Mini";
        elseif (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR') !== FALSE)
            return "Opera";
        elseif (strpos($user_agent, 'Firefox') !== FALSE)
            return 'Mozilla Firefox';
        elseif (strpos($user_agent, 'Chrome') !== FALSE)
            return 'Google Chrome';
        elseif (strpos($user_agent, 'Safari') !== FALSE)
            return "Safari";
        else
            return 'No hemos podido detectar su navegador';
    }

    function getDispositivo()
    {
        $tablet_browser = 0;
        $mobile_browser = 0;
        $body_class = 'desktop';

        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $tablet_browser++;
            $body_class = "tablet";
        }

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $mobile_browser++;
            $body_class = "mobile";
        }

        if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $mobile_browser++;
            $body_class = "mobile";
        }

        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
            'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
            'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
            'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
            'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
            'newt', 'noki', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
            'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
            'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
            'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
            'wapr', 'webc', 'winw', 'winw', 'xda ', 'xda-'
        );

        if (in_array($mobile_ua, $mobile_agents)) {
            $mobile_browser++;
        }

        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'opera mini') > 0) {
            $mobile_browser++;
            //Check for tablets on opera mini alternative headers
            $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ? $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] : (isset($_SERVER['HTTP_DEVICE_STOCK_UA']) ? $_SERVER['HTTP_DEVICE_STOCK_UA'] : ''));
            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
                $tablet_browser++;
            }
        }
        if ($tablet_browser > 0) {
            // Si es tablet has lo que necesites
            return 'Tablet';
        } else if ($mobile_browser > 0) {
            // Si es dispositivo mobil has lo que necesites
            return 'Mobil';
        } else {
            // Si es ordenador de escritorio has lo que necesites
            return 'PC';
        }
    }

    public function confirmarFactura40(Request $request, $id)
    {

        $datos = $request->except('adeudo_pago_on_line');
        //dd($datos);
        $rules = [
            'tipo_persona_id' => 'required',
            'frazon' => 'required',
            'frfc' => 'required',
            'fcalle' => 'required',
            'fno_exterior' => 'required',
            'fcolonia' => 'required',
            'festado' => 'required',
            'fpais' => 'required',
            'fcp' => 'required',
            'curp' => 'required',
            'fmail' => 'required',
            'regimen_fiscal_id' => 'required',
        ];
        $customMessages = [
            'required' => 'El campo es obligatorio, capturar un valor.'
        ];
        $request->validate($rules, $customMessages);
        //dd($v);
        $adeudoPagoOnLine = AdeudoPagoOnLine::find($id);
        $adeudo = $adeudoPagoOnLine->adeudo;
        $nivelEducativoSat = NivelEducativoSat::find($adeudo->combinacionCliente->grado->nivel_educativo_sat_id);
        $cliente = $adeudoPagoOnLine->cliente;
        $cliente->update($datos);
        $plantel = $adeudoPagoOnLine->cliente->plantel;
        $pago = $adeudoPagoOnLine->pago;
        if (!is_null($pago->uuid)) {
            dd("No es posible facturar, presentarse en Caja");
        }
        //dd('no mandar factura');
        $serie_folio = explode("-", $pago->csc_simplificado);
        if (is_null($pago->csc_simplificado)) {
            dd("problema con el consecutivo simplificado");
        }
        $caja = $adeudoPagoOnLine->caja;
        //dd($caja->toArray());
        $fecha_anio = Carbon::createFromFormat('Y-m-d', $adeudo->fecha_pago)->year;

        //Parametros para el webservice
        $parametroUrl = Param::where('llave', 'fact_global_url')->first();
        $parametroFactPrbActiva = Param::where('llave', 'fact_prb_activa')->first();
        $url = $parametroUrl->valor;
        //dd($url);
        $cuenta = $plantel->matriz->fact_global_id_cuenta;

        $cuenta_password = $plantel->matriz->fact_global_pass_cuenta;
        if ($parametroFactPrbActiva->valor == 2) {
            $p_cuenta = Param::where('llave', 'fact_global_id_cuenta_prb')->first();
            $p_cuenta_password = Param::where('llave', 'fact_global_pass_cuenta_prb')->first();
            $cuenta = $p_cuenta->valor;
            $cuenta_password = $p_cuenta_password->valor;
        }


        try {

            $fecha_solicitud_factura_tabla = date('Y-m-d H:i:s');
            $fecha_solicitud_factura_service = date('Y-m-d\TH:i:s');

            $pagos = Pago::where('caja_id', $adeudo->caja_id)->get();

            //dd($pagos->toArray());
            $total_pagos = 0;
            foreach ($pagos as $pago) {
                $total_pagos = $total_pagos + $pago->monto;
            }
            //dd($cliente->usoFactura);
            $grado = $adeudo->combinacionCliente->grado;
            if (
                is_null($grado->nivel_educativo_sat_id) or $grado->nivel_educativo_sat_id == "" or
                is_null($grado->clave_servicio) or $grado->clave_servicio == "" or
                is_null($grado->seccion) or $grado->seccion == "" or
                is_null($grado->fec_rvoe) or $grado->fec_rvoe == "" or
                is_null($grado->rvoe) or $grado->rvoe == ""
            ) {
                dd("Verificar nivel educativo sat, clave servicio, seccion, fecha RVOE o RVOE en el grado con id:" . $grado->id);
            }

            $objetosArray = array();
            //dd($adeudo->combinacionCliente->grado_id);
            if ($adeudo->combinacionCliente->grado->clave_servicio == "86121600") {
                $descripcion = $caja->cajaLn->cajaConcepto->leyenda_factura . " " . $fecha_anio;
                /*$objetosArray = array(

                    'cfdi' => array(
                        'Addenda' => array(

                            'DomicilioEmisor' => array(
                                'Calle' => $plantel->calle,
                                'CodigoPostal' => $plantel->cp,
                                'Colonia' => $plantel->colonia,
                                'Estado' => $plantel->estado,
                                'Municipio' => $plantel->municipio,
                                'NombreCliente' => $plantel->nombre_corto,
                                'NumeroExterior' => $plantel->no_ext,
                                'NumeroInterior' => $plantel->no_int,
                                'Pais' => 'Mexico',
                            ),
                            'DomicilioReceptor' => array(
                                'Calle' => $cliente->fcalle,
                                'CodigoPostal' => $cliente->fcp,
                                'Colonia' => $cliente->fcolonia,
                                'Estado' => $cliente->festado,
                                'Localidad' => $cliente->flocalidad,
                                'Municipio' => $cliente->fmunicipio,
                                'NombreCliente' => $cliente->fno_interior,
                                'NumeroExterior' => $cliente->fno_exterior,
                                'NumeroInterior' => $cliente->fno_interior,
                                'Pais' => $cliente->fpais,
                            )
                        ),
                        'ClaveCFDI' => 'FAC', //Requerido valor default para ingresos segun documento tecnico del proveedor
                        'Exportacion' => "01", //Campo Nuevo
                        //Plantel emisor de factura
                        'Emisor' => array(
                            'Nombre' => $cliente->plantel->nombre_corto,
                            'RegimenFiscal' => $cliente->plantel->regimen_fiscal, //Campo nuevo en planteles
                            'Rfc' => $cliente->plantel->matriz->rfc,
                        ),
                        //Cliente
                        'Receptor' => array(
                            'DomicilioFiscalReceptor' => $cliente->fcp, //Atributo nuevo ->fcp
                            'Nombre' => $cliente->frazon,
                            'RegimenFiscalReceptor' => $cliente->regimenFiscal->clave, //Dato nuevo
                            'Rfc' => $cliente->frfc, //'TEST010203001',
                            'UsoCFDI' => $cliente->usoFactura->clave //$adeudo->cajaConcepto->uso_factura, //campo nuevo en conceptos de caja, Definir valor Default de acuerdo al SAT
                        ),
                        //'CondicionesDePago' => 'CONDICIONES', //opcional
                        'FormaPago' => $pago->formaPago->cve_sat, //No es Opcional Documentacion erronea, llenar en tabla campo nuevo
                        'Fecha' => $fecha_solicitud_factura_service,
                        'MetodoPago' => 'PUE', //No es Opcional Documentacion erronea, Definir default segun catalogo del SAT
                        'LugarExpedicion' => $cliente->plantel->cp, //CP del plantel, debe ser valido segun catalogo del SAT
                        'Moneda' => 'MXN', //Default
                        'Referencia' => $pago->csc_simplificado,  //Definir valor
                        'Serie' => $serie_folio[0],
                        'Folio' => $serie_folio[1],
                        'TipoDeComprobante' => 'I',
                        'Serie' => $serie_folio[0],
                        'Folio' => $serie_folio[1],
                        'Conceptos' => array('Concepto40R' => array(
                            'Cantidad' => '1',
                            'ClaveProdServ' => $adeudo->combinacionCliente->grado->clave_servicio, //Definir valor defaul de acuerdo al SAT
                            'ClaveUnidad' => 'E48',
                            'NoIdentificacion'=>$caja->cajaLn->caja_concepto_id,
                            'Unidad' => 'Servicio', //Definir valor default
                            'Descripcion' => $caja->cajaLn->cajaConcepto->leyenda_factura . " " . $fecha_anio,
                            'ObjetoImp' => "02", //Campo nuevo
                            'Impuestos' => array('Traslados' => array('TrasladoConceptoR' => array( //no se manejan impuestos
                                'Base' => number_format($total_pagos, 2, '.', ''),
                                //'Importe' => '0.00',
                                'Impuesto' => '002',
                                //'TasaOCuota' => '0.000000',
                                'TipoFactor' => 'Exento'
                            ),),),
                            'InstEducativas' => array(
                                'AutRVOE' => $adeudo->combinacionCliente->grado->rvoe,
                                'CURP' => $cliente->curp,
                                'NivelEducativo' => $nivelEducativoSat->name,
                                'NombreAlumno' => $cliente->nombre . " " . $cliente->nombre2 . " " . $cliente->ape_paterno . " " . $cliente->ape_materno,
                                'RfcPago' => $cliente->frfc
                            ),
                            //'NoIdentificacion' => '00003', //Opcional
                            'Importe' => number_format($total_pagos, 2, '.', ''),
                            'ValorUnitario' => number_format($total_pagos, 2, '.', '')
                        ),),
                        'SubTotal' => number_format($total_pagos, 2, '.', ''),
                        'Total' => number_format($total_pagos, 2, '.', '')
                    )
                );*/
            } elseif ($adeudo->combinacionCliente->grado->clave_servicio == "86121700") {
                $descripcion = $cliente->nombre . " " . $cliente->nombre2 . " " . $cliente->ape_paterno . " " . $cliente->ape_materno . " " .
                    $caja->cajaLn->cajaConcepto->leyenda_factura . " " . $fecha_anio . " " .
                    $adeudo->combinacionCliente->grado->name . " " .
                    "CURP: " . $cliente->curp . " " .
                    "RVOE: " . $adeudo->combinacionCliente->grado->rvoe;
                /*$objetosArray = array(
                    'cfdi' => array(
                        'Addenda' => array(
                            'DomicilioEmisor' => array(
                                'Calle' => $plantel->calle,
                                'CodigoPostal' => $plantel->cp,
                                'Colonia' => $plantel->colonia,
                                'Estado' => $plantel->estado,
                                'Municipio' => $plantel->municipio,
                                'NombreCliente' => $plantel->nombre_corto,
                                'NumeroExterior' => $plantel->no_ext,
                                'NumeroInterior' => $plantel->no_int,
                                'Pais' => 'Mexico',
                            ),
                            'DomicilioReceptor' => array(
                                'Calle' => $cliente->fcalle,
                                'CodigoPostal' => $cliente->fcp,
                                'Colonia' => $cliente->fcolonia,
                                'Estado' => $cliente->festado,
                                'Localidad' => $cliente->flocalidad,
                                'Municipio' => $cliente->fmunicipio,
                                'NombreCliente' => $cliente->fno_interior,
                                'NumeroExterior' => $cliente->fno_exterior,
                                'NumeroInterior' => $cliente->fno_interior,
                                'Pais' => $cliente->fpais,
                            )
                        ),
                        'ClaveCFDI' => 'FAC', //Requerido valor default para ingresos segun documento tecnico del proveedor
                        'Exportacion' => "01", //Campo Nuevo
                        //Plantel emisor de factura
                        'Emisor' => array(
                            'Nombre' => $cliente->plantel->matriz->nombre_corto,
                            'RegimenFiscal' => $cliente->plantel->matriz->regimen_fiscal, //Campo nuevo en planteles
                            'Rfc' => $cliente->plantel->matriz->rfc,
                        ),
                        //Cliente
                        'Receptor' => array(
                            'DomicilioFiscalReceptor' => $cliente->fcp, //Atributo nuevo ->fcp
                            'Nombre' => $cliente->frazon,
                            'RegimenFiscalReceptor' => $cliente->regimenFiscal->clave, //Dato nuevo
                            'Rfc' => $cliente->frfc, //'TEST010203001',
                            'UsoCFDI' => $cliente->usoFactura->clave //$adeudo->cajaConcepto->uso_factura, //campo nuevo en conceptos de caja, Definir valor Default de acuerdo al SAT
                        ),
                        //'CondicionesDePago' => 'CONDICIONES', //opcional
                        'FormaPago' => $pago->formaPago->cve_sat, //No es Opcional Documentacion erronea, llenar en tabla campo nuevo
                        'Fecha' => $fecha_solicitud_factura_service,
                        'MetodoPago' => 'PUE', //No es Opcional Documentacion erronea, Definir default segun catalogo del SAT
                        'LugarExpedicion' => $cliente->plantel->cp, //CP del plantel, debe ser valido segun catalogo del SAT
                        'Moneda' => 'MXN', //Default
                        'Referencia' => $pago->csc_simplificado,  //Definir valor
                        'Serie' => $serie_folio[0],
                        'Folio' => $serie_folio[1],
                        'TipoDeComprobante' => 'I',
                        'Conceptos' => array('Concepto40R' => array(
                            'Cantidad' => '1',
                            'ClaveProdServ' => $adeudo->combinacionCliente->grado->clave_servicio, //Definir valor defaul de acuerdo al SAT
                            'ClaveUnidad' => 'E48',
                            'NoIdentificacion'=>$caja->cajaLn->caja_concepto_id,
                            'Unidad' => 'Servicio', //Definir valor default
                            'Descripcion' => $cliente->nombre . " " . $cliente->nombre2 . " " . $cliente->ape_paterno . " " . $cliente->ape_materno . PHP_EOL .
                                $caja->cajaLn->cajaConcepto->leyenda_factura . " " . $fecha_anio . PHP_EOL .
                                $adeudo->combinacionCliente->grado->name . PHP_EOL .
                                "CURP: " . $cliente->curp . PHP_EOL .
                                "RVOE: " . $adeudo->combinacionCliente->grado->rvoe,
                            'ObjetoImp' => "02", //Campo nuevo
                            'Impuestos' => array('Traslados' => array('TrasladoConceptoR' => array( //no se manejan impuestos
                                'Base' => number_format($total_pagos, 2, '.', ''),
                                //'Importe' => '0.00',
                                'Impuesto' => '002',
                                //'TasaOCuota' => '0.000000',
                                'TipoFactor' => 'Exento'
                            ),),),
                            'InstEducativas' => array(
                                'AutRVOE' => $adeudo->combinacionCliente->grado->rvoe,
                                'CURP' => $cliente->curp,
                                'NivelEducativo' => $nivelEducativoSat->name,
                                'NombreAlumno' => $cliente->nombre . " " . $cliente->nombre2 . " " . $cliente->ape_paterno . " " . $cliente->ape_materno,
                                'RfcPago' => $cliente->frfc
                            ),
                            //'NoIdentificacion' => '00003', //Opcional
                            'Importe' => number_format($total_pagos, 2, '.', ''),
                            'ValorUnitario' => number_format($total_pagos, 2, '.', '')
                        ),),
                        'SubTotal' => number_format($total_pagos, 2, '.', ''),
                        'Total' => number_format($total_pagos, 2, '.', '')
                    )
                );*/
            }
            $objetosArray = array(
                'cfdi' => array(
                    'Addenda' => array(
                        'DomicilioEmisor' => array(
                            'Calle' => $plantel->calle,
                            'CodigoPostal' => $plantel->cp,
                            'Colonia' => $plantel->colonia,
                            'Estado' => $plantel->estado,
                            'Municipio' => $plantel->municipio,
                            'NombreCliente' => $plantel->nombre_corto,
                            'NumeroExterior' => $plantel->no_ext,
                            'NumeroInterior' => $plantel->no_int,
                            'Pais' => 'Mexico',
                        ),
                        'DomicilioReceptor' => array(
                            'Calle' => $cliente->fcalle,
                            'CodigoPostal' => $cliente->fcp,
                            'Colonia' => $cliente->fcolonia,
                            'Estado' => $cliente->festado,
                            'Localidad' => $cliente->flocalidad,
                            'Municipio' => $cliente->fmunicipio,
                            'NombreCliente' => $cliente->fno_interior,
                            'NumeroExterior' => $cliente->fno_exterior,
                            'NumeroInterior' => $cliente->fno_interior,
                            'Pais' => $cliente->fpais,
                        )
                    ),
                    'ClaveCFDI' => 'FAC', //Requerido valor default para ingresos segun documento tecnico del proveedor
                    'Exportacion' => "01", //Campo Nuevo
                    //Plantel emisor de factura
                    'Emisor' => array(
                        'Nombre' => $cliente->plantel->matriz->nombre_corto,
                        'RegimenFiscal' => $cliente->plantel->matriz->regimen_fiscal, //Campo nuevo en planteles
                        'Rfc' => $cliente->plantel->matriz->rfc,
                    ),
                    //Cliente
                    'Receptor' => array(
                        'DomicilioFiscalReceptor' => $cliente->fcp, //Atributo nuevo ->fcp
                        'Nombre' => $cliente->frazon,
                        'RegimenFiscalReceptor' => $cliente->regimenFiscal->clave, //Dato nuevo
                        'Rfc' => $cliente->frfc, //'TEST010203001',
                        'UsoCFDI' => $cliente->usoFactura->clave //$adeudo->cajaConcepto->uso_factura, //campo nuevo en conceptos de caja, Definir valor Default de acuerdo al SAT
                    ),
                    'CondicionesDePago' => 'CONTADO', //opcional
                    'FormaPago' => $pago->formaPago->cve_sat, //No es Opcional Documentacion erronea, llenar en tabla campo nuevo
                    'Fecha' => $fecha_solicitud_factura_service,
                    'MetodoPago' => 'PUE', //No es Opcional Documentacion erronea, Definir default segun catalogo del SAT
                    'LugarExpedicion' => $cliente->plantel->cp, //CP del plantel, debe ser valido segun catalogo del SAT
                    'Moneda' => 'MXN', //Default
                    'Referencia' => $pago->csc_simplificado,  //Definir valor
                    'Serie' => $serie_folio[0],
                    'Folio' => $serie_folio[1],
                    'TipoDeComprobante' => 'I',
                    'Conceptos' => array('Concepto40R' => array(
                        'Cantidad' => '1',
                        'ClaveProdServ' => $adeudo->combinacionCliente->grado->clave_servicio, //Definir valor defaul de acuerdo al SAT
                        'ClaveUnidad' => 'E48',
                        'NoIdentificacion' => $caja->cajaLn->caja_concepto_id,
                        'Unidad' => 'Servicio', //Definir valor default
                        'Descripcion' => $descripcion,
                        'ObjetoImp' => "02", //Campo nuevo
                        'Impuestos' => array('Traslados' => array('TrasladoConceptoR' => array( //no se manejan impuestos
                            'Base' => number_format($total_pagos, 2, '.', ''),
                            //'Importe' => '0.00',
                            'Impuesto' => '002',
                            //'TasaOCuota' => '0.000000',
                            'TipoFactor' => 'Exento'
                        ),),),
                        'InstEducativas' => array(
                            'AutRVOE' => $adeudo->combinacionCliente->grado->rvoe,
                            'CURP' => $cliente->curp,
                            'NivelEducativo' => $nivelEducativoSat->name,
                            'NombreAlumno' => $cliente->nombre . " " . $cliente->nombre2 . " " . $cliente->ape_paterno . " " . $cliente->ape_materno,
                            'RfcPago' => $cliente->frfc
                        ),
                        //'NoIdentificacion' => '00003', //Opcional
                        'Importe' => number_format($total_pagos, 2, '.', ''),
                        'ValorUnitario' => number_format($total_pagos, 2, '.', '')
                    ),),
                    'SubTotal' => number_format($total_pagos, 2, '.', ''),
                    'Total' => number_format($total_pagos, 2, '.', '')
                )
            );

            //dd($objetosArray);

            $comprobante = array(
                'Version' => '4.0',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xmlns:cfdi' => 'http://www.sat.gob.mx/cfd/4',
                'xmlns:iedu' => "http://www.sat.gob.mx/iedu",
                'xsi:schemaLocation' => 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://www.sat.gob.mx/iedu http://www.sat.gob.mx/sitio_internet/cfd/iedu/iedu.xsd',
                'Certificado' => '', //?
                'NoCertificado' => '', //?
                'Serie' => $objetosArray['cfdi']['Serie'], //?
                'Folio' => $objetosArray['cfdi']['Folio'], //?
                'Fecha' => $objetosArray['cfdi']['Fecha'],
                'Sello' => '',
                'FormaPago' => $objetosArray['cfdi']['FormaPago'],
                'CondicionesDePago' => 'CONTADO',
                'TipoCambio' => 1,
                'SubTotal' => $objetosArray['cfdi']['SubTotal'],
                'Moneda' => $objetosArray['cfdi']['Moneda'],
                'Total' => $objetosArray['cfdi']['Total'],
                'TipoDeComprobante' => $objetosArray['cfdi']['TipoDeComprobante'],
                'Exportacion' => $objetosArray['cfdi']['Exportacion'],
                'MetodoPago' => $objetosArray['cfdi']['MetodoPago'],
                'LugarExpedicion' => $objetosArray['cfdi']['LugarExpedicion']
            );

            $emisor = array(
                'Rfc' => $objetosArray['cfdi']['Emisor']['Rfc'],
                'Nombre' => $objetosArray['cfdi']['Emisor']['Nombre'],
                'RegimenFiscal' => $objetosArray['cfdi']['Emisor']['RegimenFiscal']
            );

            //dd($objetosArray['cfdi']);

            $receptor = array(
                'Rfc' => strtoupper($objetosArray['cfdi']['Receptor']['Rfc']),
                'Nombre' => strtoupper($objetosArray['cfdi']['Receptor']['Nombre']),
                'DomicilioFiscalReceptor' => strtoupper($objetosArray['cfdi']['Receptor']['DomicilioFiscalReceptor']), //Por definir
                'UsoCFDI' => $objetosArray['cfdi']['Receptor']['UsoCFDI'], //Por definir
                'RegimenFiscalReceptor' => $objetosArray['cfdi']['Receptor']['RegimenFiscalReceptor'], //Por definir
            );

            //dd($receptor);

            $concepto = array(
                'ClaveProdServ' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['ClaveProdServ'],
                'NoIdentificacion' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['NoIdentificacion'], //??preguntar
                'Cantidad' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['Cantidad'],
                'ClaveUnidad' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['ClaveUnidad'],
                'Unidad' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['Unidad'],
                'Descripcion' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['Descripcion'],
                'ValorUnitario' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['ValorUnitario'],
                'Importe' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['Importe'],
                'Descuento' => '0.00',
                'ObjetoImp' => "02",
                'Base' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['Impuestos']['Traslados']['TrasladoConceptoR']['Base'], // desglose de impuestos por concepto
                'Impuesto' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['Impuestos']['Traslados']['TrasladoConceptoR']['Impuesto'], // desglose de impuestos por concepto
                'TipoFactor' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['Impuestos']['Traslados']['TrasladoConceptoR']['TipoFactor'], // desglose de impuestos por concepto
                'AutRVOE' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['InstEducativas']['AutRVOE'],
                'CURP' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['InstEducativas']['CURP'],
                'NivelEducativo' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['InstEducativas']['NivelEducativo'],
                'NombreAlumno' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['InstEducativas']['NombreAlumno'],
                'RfcPago' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['InstEducativas']['RfcPago'],
            );

            // desglose de impuestos de la factura
            $impuestos = array(
                'TotalImpuestosTrasladados' => '0.00',
                'Base' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['Impuestos']['Traslados']['TrasladoConceptoR']['Base'], // desglose de impuestos por concepto
                //'Importe' => '0.00',
                'Impuesto' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['Impuestos']['Traslados']['TrasladoConceptoR']['Impuesto'], // desglose de impuestos por concepto
                //'TasaOCuota' => '0.000000',
                'TipoFactor' => $objetosArray['cfdi']['Conceptos']['Concepto40R']['Impuestos']['Traslados']['TrasladoConceptoR']['TipoFactor'], // desglose de impuestos por concepto
            );

            //Log::info($objetosArray);
            $xmlFactura = $this->crearXmlFactura40($comprobante, $fecha_solicitud_factura_service, $emisor, $receptor, $concepto, $impuestos);
            //dd($xmlFactura);
            Log::info($xmlFactura);

            $data = array();
            //dd($parametroFactPrbActiva->valor);
            if ($parametroFactPrbActiva->valor == 1) {
                //dd('decarga');
                ob_end_clean();
                ob_start();
                header('Content-Type: application/xml; charset=UTF-8');
                header('Content-Encoding: UTF-8');
                header("Content-Disposition: attachment;filename=factura.xml");
                header('Expires: 0');
                header('Pragma: cache');
                header('Cache-Control: private');
                echo $xmlFactura;
                dd("aqui ya descargo");
            } else if ($parametroFactPrbActiva->valor == 2) {
                $data = array(
                    "cti" => $cuenta,
                    "pwd" => $cuenta_password,
                    "idd" => "",
                    "ncer" => "",
                    "nb64" => "false",
                    "xml" => base64_encode($xmlFactura)
                    //"xml" => $xmlFactura
                );
                //dd($data);

                $client = new Client(['base_uri' => $url]);
                $response = $client->post("sellar-y-timbrar/", [
                    // un array con la data de los headers como tipo de peticion, etc.
                    //'headers' => ['foo' => 'bar'],
                    // array de datos del formulario
                    'json' => $data
                ]);
                $objR = json_decode($response->getBody()->getContents());
                //dd($objR);
                if ($objR->success == 0) {


                    $destinatario = "linares82@gmail.com";
                    $n = Auth::user()->name;
                    $asunto = "Problema Facturacion";
                    if (isset($objR->message)) {
                        $contenido = $objR->message . $xmlFactura;
                    }
                    if (isset($objR->data)) {
                        $contenido = $objR->data . $xmlFactura;
                    }
                    $from = "ohpelayo@gmail.com";
                    $contenido = $contenido . " " . $xmlFactura;
                    $contenido = $contenido . ' ' . $cuenta . " " . $cuenta_password;


                    $data = array('contenido' => $contenido, 'nombre' => $n, 'correo' => $from);
                    $r = \Mail::send('correos.errorApiFiolsDigitales', $data, function ($message)
                    use ($asunto, $destinatario, $n, $from) {
                        $message->from(env('MAIL_FROM_ADDRESS', 'hola@grupocedva.com'), env('MAIL_FROM_NAME', 'Grupo CEDVA'));
                        $message->to($destinatario, $n)->subject($asunto);
                        $message->replyTo($from);
                    });

                    Log::info($contenido);
                    dd($objR);
                } else {
                    $pagos1 = Pago::where('caja_id', $adeudo->caja_id)->whereNull('deleted_at')->get();
                    foreach ($pagos1 as $pago1) {
                        $pago1->uuid = $objR->data->uuid;
                        $pago1->xml = base64_decode($objR->data->xml);
                        $pago1->fecha_solicitud_factura = $fecha_solicitud_factura_tabla;

                        $pago1->save();

                        //Envio de correo por parte del proveedor
                        $data = array(
                            "uid" => $cuenta,
                            "pwd" => $cuenta_password,
                            "doc" => $pago1->uuid,
                            "to" => $cliente->fmail,
                            //"from": "Nombre para mostrar del remitente (opcional)",
                            //"cc"=>"Dirección de correo cc (opcional)",opcional
                            //"cco"=>"Dirección de correo de copia oculta (opcional)",
                            //"reply"=>"Dirección de correo de respuesta (opcional)",
                            "subject" => "Factura" . $plantel->nombre_corto,
                            //"body"=>"Cuerpo del mensaje de correo (opcional)",
                            //"tpo"=>"cfdi/cr (opcional)",
                            //"res"=>"(Opcional) tipo de resultado deseado",
                            "res" => "both",
                            //"pln"=>"(Opcional) Identificador de plantilla de representación impresa"
                        );
                        $client = new Client(['base_uri' => $url]);
                        $response = $client->post("enviar/", [
                            // un array con la data de los headers como tipo de peticion, etc.
                            //'headers' => ['foo' => 'bar'],
                            // array de datos del formulario
                            //'json' => $data
                            'form_params' => $data
                        ]);
                        $objR = json_decode($response->getBody()->getContents());
                        /*if($objR->success==0){
                            Log::info('uuid: '.$pago1->uuid.' Peticion de correo fallida');
                            echo 'Problema en el envio a su correo, pero puede descargar sus archivos xml y pdf lista de pagos realizados.';
                            echo "<a href=\"{{route('fichaAdeudos.index')\"}} > ir a lista de pagos realizados </a>";
                        }else{
                            Log::info('uuid: '.$pago1->uuid.' Peticion de correo exitosa');
                        }*/
                    }
                }
            } else {
                $data = array(
                    "cti" => $cuenta,
                    "pwd" => $cuenta_password,
                    "idd" => "",
                    "ncer" => "",
                    "nb64" => "false",
                    "xml" => base64_encode($xmlFactura)
                    //"xml" => $xmlFactura
                );
                //dd($data);
                $client = new Client(['base_uri' => $url]);
                $response = $client->post("sellar-y-timbrar/", [
                    // un array con la data de los headers como tipo de peticion, etc.
                    //'headers' => ['foo' => 'bar'],
                    // array de datos del formulario
                    'json' => $data
                ]);
                $objR = json_decode($response->getBody()->getContents());
                if ($objR->success == 0) {
                    //$facturaG->error_last = $objR->data;
                    //$facturaG->save();
                    $destinatario = "linares82@gmail.com";
                    $n = Auth::user()->name;
                    $asunto = "Problema Folios Digitales";
                    if (isset($objR->message)) {
                        $contenido = $objR->message . $xmlFactura;
                    }
                    if (isset($objR->data)) {
                        $contenido = $objR->data . $xmlFactura;
                    }
                    $from = "ohpelayo@gmail.com";
                    $contenido = $contenido . " " . $xmlFactura;
                    $contenido = $contenido . ' ' . $cuenta . " " . $cuenta_password;
                    //dd(env('MAIL_FROM_ADDRESS'));

                    $data = array('contenido' => $contenido, 'nombre' => $n, 'correo' => $from);
                    $r = \Mail::send('correos.errorApiFiolsDigitales', $data, function ($message)
                    use ($asunto, $destinatario, $n, $from) {
                        $message->from(env('MAIL_FROM_ADDRESS', 'hola@grupocedva.com'), env('MAIL_FROM_NAME', 'Grupo CEDVA'));
                        $message->to($destinatario, $n)->subject($asunto);
                        $message->replyTo($from);
                    });

                    Log::info($contenido);
                    dd($objR);
                } else {
                    $pagos1 = Pago::where('caja_id', $adeudo->caja_id)->whereNull('deleted_at')->get();
                    foreach ($pagos1 as $pago1) {
                        $pago1->uuid = $objR->data->uuid;
                        $pago1->xml = base64_decode($objR->data->xml);
                        $pago1->fecha_solicitud_factura = $fecha_solicitud_factura_tabla;

                        $pago1->save();


                        //Envio de correo por parte del proveedor
                        $data = array(
                            "uid" => $pago1->caja->plantel->matriz->fact_global_id_usu,
                            "pwd" => $pago1->caja->plantel->matriz->fact_global_pass_usu,
                            "doc" => $pago1->uuid,
                            "to" => $cliente->fmail,
                            //"from": "Nombre para mostrar del remitente (opcional)",
                            //"cc"=>"Dirección de correo cc (opcional)",opcional
                            //"cco"=>"Dirección de correo de copia oculta (opcional)",
                            //"reply"=>"Dirección de correo de respuesta (opcional)",
                            "subject" => "Factura" . $plantel->nombre_corto,
                            //"body"=>"Cuerpo del mensaje de correo (opcional)",
                            //"tpo"=>"cfdi/cr (opcional)",
                            //"res"=>"(Opcional) tipo de resultado deseado",
                            "res" => "both",
                            //"pln"=>"(Opcional) Identificador de plantilla de representación impresa"
                        );
                        $client = new Client(['base_uri' => $url]);
                        $response = $client->post("enviar/", [
                            // un array con la data de los headers como tipo de peticion, etc.
                            //'headers' => ['foo' => 'bar'],
                            // array de datos del formulario
                            //'json' => $data
                            'form_params' => $data
                        ]);

                        $objR = json_decode($response->getBody()->getContents());
                        /*if($objR->success==0){
                            Log::info('uuid: '.$pago1->uuid.' Peticion de correo fallida');
                            echo 'Problema en el envio a su correo, pero puede descargar sus archivos xml y pdf lista de pagos realizados.';
                            echo "<a href=\"{{route('fichaAdeudos.index')\"}} > ir a lista de pagos realizados </a>";
                        }else{
                            Log::info('uuid: '.$pago1->uuid.' Peticion de correo exitosa');
                        }*/
                    }
                }
            }
        } catch (\Exception $e) {
            //echo $e->getMessage();
            //dd($e);
            $destinatario = "linares82@gmail.com";
            $n = Auth::user()->name;
            $asunto = "Problema Facturacion error try catch";
            $contenido = $e->getMessage();
            $from = "ohpelayo@gmail.com";
            $contenido = $contenido;
            $contenido = $contenido . ' ' . $cuenta . " " . $cuenta_password;
            //dd(env('MAIL_FROM_ADDRESS'));

            $data = array('contenido' => $contenido, 'nombre' => $n, 'correo' => $from);
            $r = \Mail::send('correos.errorApiFiolsDigitales', $data, function ($message)
            use ($asunto, $destinatario, $n, $from) {
                $message->from(env('MAIL_FROM_ADDRESS', 'hola@grupocedva.com'), env('MAIL_FROM_NAME', 'Grupo CEDVA'));
                $message->to($destinatario, $n)->subject($asunto);
                $message->replyTo($from);
            });
            dd($e);
        }
        return redirect()->route('fichaAdeudos.index');
        //return redirect()->route('fichaAdeudos.index');
        //dd($cliente->toArray());
        //dd($adeudoPagoOnLine);
    }

    public function getFacturaPdfByUuid40(Request $request)
    {
        $datos = $request->all();
        $pago = Pago::where('uuid', $datos['uuid'])->first();
        $url_aux = Param::where('llave', 'fact_global_url')->first();
        $url = $url_aux->valor . "descargar/";
        //dd($url);
        $fact_prb_activa = Param::where('llave', 'fact_prb_activa')->first();

        $data = array();

        if ($fact_prb_activa->valor == 1) {
            $fact_global_id_usu_prb = Param::where('llave', 'fact_global_id_usu_prb')->first();
            $fact_global_pass_usu_prb = Param::where('llave', 'fact_global_pass_usu_prb')->first();
            $data = array(
                "uid" => $fact_global_id_usu_prb->valor,
                "pwd" => $fact_global_pass_usu_prb->valor,
                "doc" => $pago->uuid,
                "res" => "ziplnk",
                "tpo" => "",
                "pln" => ""
            );
        } else {
            $data = array(
                "uid" => $pago->caja->plantel->matriz->fact_global_id_usu,
                "pwd" => $pago->caja->plantel->matriz->fact_global_pass_usu,
                "doc" => $pago->uuid,
                "res" => "ziplnk",
                "tpo" => "",
                "pln" => ""
            );
        }

        //dd($data);
        $opciones = array(
            "http" => array(
                "header" => "Content-type: application/x-www-form-urlencoded\r\n",
                "method" => "POST",
                "content" => http_build_query($data), # Agregar el contenido definido antes
            ),
        );
        # Preparar petición
        $contexto = stream_context_create($opciones);

        //*****para ver el flujo durante la invocación
        $flujo = fopen($url, 'r', false, $contexto);
        stream_set_blocking($flujo, false);
        //***************

        //*******************respuestas en formato json
        $resultado = file_get_contents($url, false, $contexto);
        //dd($contexto);

        $data = json_decode($resultado, true);

        echo json_encode($data);

        if ($data["success"] == false) {
            echo "Error:" . $data["message"];
            exit;
        }

        # si fue existoso
        if ($data["success"] == true) {
            /*echo "<br>";
			echo "<br>";
			echo "<label>Puede descargar el zip dando click en el botón </label>";
			echo "<a href='".$data["data"]["link"]."'><button style='background:green;'>Descargar</button></a>";
			*/
            return redirect()->away($data["data"]["link"]);
        } {
            dd('Recurso no encontrado');
        }
    }

    public function crearXmlFactura40($comprobante, $fecha_solicitud_factura_service, $emisor, $receptor, $concepto, $impuestos)
    {
        //dd($impuestos);
        $objetoXML = new XMLWriter();

        $objetoXML->openMemory();
        $objetoXML->setIndent(true);
        $objetoXML->setIndentString("\t");
        $objetoXML->startDocument('1.0', 'utf-8');

        $objetoXML->startElement("cfdi:Comprobante");

        $objetoXML->writeAttribute("Version", $comprobante["Version"]);
        $objetoXML->writeAttribute("xmlns:xsi", $comprobante["xmlns:xsi"]);
        $objetoXML->writeAttribute("xmlns:cfdi", $comprobante["xmlns:cfdi"]);
        $objetoXML->writeAttribute("xmlns:iedu", $comprobante["xmlns:iedu"]);
        $objetoXML->writeAttribute("xsi:schemaLocation", $comprobante["xsi:schemaLocation"]);
        $objetoXML->writeAttribute("Serie", $comprobante["Serie"]);
        $objetoXML->writeAttribute("Folio", $comprobante["Folio"]);
        $objetoXML->writeAttribute("Fecha", $fecha_solicitud_factura_service);
        $objetoXML->writeAttribute("NoCertificado", $comprobante["NoCertificado"]);
        $objetoXML->writeAttribute("Certificado", $comprobante["Certificado"]);
        $objetoXML->writeAttribute("FormaPago", $comprobante["FormaPago"]);
        $objetoXML->writeAttribute("CondicionesDePago", $comprobante["CondicionesDePago"]);
        $objetoXML->writeAttribute("TipoCambio", $comprobante["TipoCambio"]);
        $objetoXML->writeAttribute("SubTotal", $comprobante["SubTotal"]);
        $objetoXML->writeAttribute("Moneda", $comprobante["Moneda"]);
        $objetoXML->writeAttribute("Total", $comprobante["Total"]);
        $objetoXML->writeAttribute("TipoDeComprobante", $comprobante["TipoDeComprobante"]);
        $objetoXML->writeAttribute("Exportacion", $comprobante["Exportacion"]);
        $objetoXML->writeAttribute("MetodoPago", $comprobante["MetodoPago"]);
        $objetoXML->writeAttribute("LugarExpedicion", $comprobante["LugarExpedicion"]);

        $objetoXML->startElement('cfdi:Emisor');
        $objetoXML->writeAttribute("Rfc", $emisor["Rfc"]);
        $objetoXML->writeAttribute("Nombre", $emisor["Nombre"]);
        $objetoXML->writeAttribute("RegimenFiscal", $emisor["RegimenFiscal"]);
        $objetoXML->endElement(); // Final del elemento que cubre todos los miembros técnicos.

        //dd($receptor);
        $objetoXML->startElement('cfdi:Receptor');
        $objetoXML->writeAttribute("Rfc", $receptor["Rfc"]);
        $objetoXML->writeAttribute("Nombre", $receptor["Nombre"]);
        $objetoXML->writeAttribute("DomicilioFiscalReceptor", $receptor["DomicilioFiscalReceptor"]);
        $objetoXML->writeAttribute("UsoCFDI", $receptor["UsoCFDI"]);
        $objetoXML->writeAttribute("RegimenFiscalReceptor", $receptor["RegimenFiscalReceptor"]);
        $objetoXML->endElement(); // Final del elemento que cubre todos los miembros técnicos.

        $objetoXML->startElement('cfdi:Conceptos');
        //foreach ($conceptos as $concepto) {
        $objetoXML->startElement('cfdi:Concepto');
        $objetoXML->writeAttribute("ClaveProdServ", $concepto["ClaveProdServ"]);
        $objetoXML->writeAttribute("NoIdentificacion", $concepto["NoIdentificacion"]);
        $objetoXML->writeAttribute("Cantidad", $concepto["Cantidad"]);
        $objetoXML->writeAttribute("ClaveUnidad", $concepto["ClaveUnidad"]);
        //$objetoXML->writeAttribute("Unidad", $concepto["Unidad"]);
        $objetoXML->writeAttribute("Descripcion", $concepto["Descripcion"]);
        $objetoXML->writeAttribute("ValorUnitario", $concepto["ValorUnitario"]);
        $objetoXML->writeAttribute("Importe", $concepto["Importe"]);
        $objetoXML->writeAttribute("ObjetoImp", $concepto["ObjetoImp"]);
        $objetoXML->startElement('cfdi:Impuestos');
        $objetoXML->startElement('cfdi:Traslados');
        $objetoXML->startElement('cfdi:Traslado');
        $objetoXML->writeAttribute("Base", $concepto["Base"]);
        $objetoXML->writeAttribute("Impuesto", $concepto["Impuesto"]);
        $objetoXML->writeAttribute("TipoFactor", $concepto["TipoFactor"]);
        $objetoXML->endElement(); //Fin traslado
        $objetoXML->endElement(); //Fin traslados
        $objetoXML->endElement(); //Fin Impuestos
        if ($concepto["NivelEducativo"] <> "Licenciatura") { //27-01-2023 se diferencia Licenciatura por que no lleva complemento y bachillerato si lleva
            $objetoXML->startElement('cfdi:ComplementoConcepto');
            $objetoXML->startElement('iedu:instEducativas');
            $objetoXML->writeAttribute("version", "1.0");
            $objetoXML->writeAttribute("nombreAlumno", $concepto["NombreAlumno"]);
            $objetoXML->writeAttribute("CURP", $concepto["CURP"]);
            $objetoXML->writeAttribute("nivelEducativo", $concepto["NivelEducativo"]);
            $objetoXML->writeAttribute("autRVOE", $concepto["AutRVOE"]);
            $objetoXML->writeAttribute("rfcPago", $concepto["RfcPago"]);
            $objetoXML->endElement(); // Fin InsEducativas
            $objetoXML->endElement(); //Fin Complemento
        }
        $objetoXML->endElement(); // Fin concepto

        $objetoXML->endElement(); // fin conceptos

        $objetoXML->startElement('cfdi:Impuestos');
        //$objetoXML->writeAttribute("TotalImpuestosTrasladados", $impuestos["TotalImpuestosTrasladados"]); //modificacion 16.08.2023
        $objetoXML->startElement('cfdi:Traslados');
        $objetoXML->startElement('cfdi:Traslado');
        $objetoXML->writeAttribute("Base", $impuestos["Base"]);
        $objetoXML->writeAttribute("Impuesto", $impuestos["Impuesto"]);
        $objetoXML->writeAttribute("TipoFactor", $impuestos["TipoFactor"]);
        //$objetoXML->writeAttribute("TasaOCuota", $impuestos["TasaOCuota"]);
        //$objetoXML->writeAttribute("Importe", $impuestos["Importe"]);
        $objetoXML->endElement(); //Fin traslado
        $objetoXML->endElement(); //Fin traslados
        $objetoXML->endElement(); //Fin impuestos
        $objetoXML->fullEndElement(); //

        $objetoXML->endDocument(); // Final del documento

        $content = $objetoXML->outputMemory();

        //dd(mb_convert_encoding( $content, 'ISO-8859-1','HTML-ENTITIES'));
        return mb_convert_encoding($content, 'ISO-8859-1', 'HTML-ENTITIES');
    }

    public function validaEntregaDocs3Meses($cliente)
    {
        //Log::info($cliente);
        //return true;
        $cliente = Cliente::find($cliente);

        $dentro3Meses = false;

        if (!is_null($cliente->matricula) or $cliente->matricula <> "") {
            $mesActual = Carbon::createFromFormat('Y-m-d', date('Y-m-d'))->month;
            $anioActual = Carbon::createFromFormat('Y-m-d', date('Y-m-d'))->year;

            $mesMatricula = intval(substr($cliente->matricula, 0, 2));
            $anioMatricula = intval("20" . substr($cliente->matricula, 2, 2));

            if (
                $anioActual == $anioMatricula and
                $mesActual <= $mesMatricula
            ) {
                $dentro3Meses = true;
            } elseif (
                $anioActual == $anioMatricula and
                $mesActual > $mesMatricula and
                ($mesActual - $mesMatricula) <= 3
            ) {
                $dentro3Meses = true;
            } elseif (
                $anioActual > $anioMatricula and
                $mesActual < $mesMatricula and $mesActual <= 3 and
                ($anioActual - $anioMatricula) == 1 and
                ($mesActual - $mesMatricula) * -1 <= 3
            ) {
                $dentro3Meses = true;
            } elseif (
                $anioActual > $anioMatricula and
                $mesActual > $mesMatricula and
                ($anioActual - $anioMatricula) == 1 and
                ($mesActual - $mesMatricula) <= 3
            ) {
                $dentro3Meses = true;
            }
        } else {
            $dentro3Meses = true;
        }

        return $dentro3Meses;
    }
}
