<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Adeudo;
use App\AdeudoPagoOnLine;
use App\AutorizacionBeca;
use App\Caja;
use App\CajaLn;
use App\Cliente;
use App\CombinacionCliente;
use App\CuentasEfectivo;
use App\Empleado;
use App\ImpresionTicket;
use App\Pago;
use App\Param;
use App\PeticionMultipago;
use App\Plantel;
use App\PromoPlanLn;
use App\SuccessMultipago;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;
use Luecano\NumeroALetras\NumeroALetras;

class FichaPagosController extends Controller
{
    public function index()
    {
        $cliente = Cliente::where('matricula', Auth::user()->name)->first();
        /*$adeudos = Adeudo::where('adeudos.cliente_id', $cliente->id)
            ->join('combinacion_clientes as cc', 'cc.id', '=', 'adeudos.combinacion_cliente_id')
            ->whereNull('cc.deleted_at')
            ->whereNull('adeudos.deleted_at')
            ->get();*/
        $this->actualizarAdeudosPagos($cliente->id, $cliente->plantel_id);
        $adeudo_pago_online = AdeudoPagoOnLine::where('matricula', $cliente->matricula)->get();

        return view('fichaPagos.index', compact('adeudos', 'cliente', 'adeudo_pago_online'));
    }

    public function actualizarAdeudosPagos($cliente, $plantel)
    {

        $plantel = Plantel::find($plantel);
        $conceptosValidos = $plantel->conceptoMultipagos->pluck('id');
        $adeudos = Adeudo::select('adeudos.*')
            ->join('caja_conceptos as cajaCon', 'cajaCon.id', '=', 'adeudos.caja_concepto_id')
            ->whereIn('cajaCon.cve_multipagos', $conceptosValidos)
            ->where('adeudos.cliente_id', $cliente)
            ->whereNull('adeudos.deleted_at')
            ->join('combinacion_clientes as cc', 'cc.id', '=', 'adeudos.combinacion_cliente_id')
            ->whereNull('cc.deleted_at')
            ->with('caja')
            ->with('cliente')
            ->with('pagoOnLine')
            ->get();
        //dd($adeudos->toArray());
        foreach ($adeudos as $adeudo) {
            //dd($adeudo->pagoOnLine);
            $adeudo_pago_online = optional($adeudo)->pagoOnLine;
            //$adeudo_pago_online = AdeudoPagoOnLine::where('adeudo_id', $adeudo->id)->first();
            if ($adeudo->pagado_bnd == 1) {

                if (is_null($adeudo_pago_online)) {
                    //dd($adeudo->caja->pago);
                    $input['matricula'] = $adeudo->cliente->matricula;
                    $input['adeudo_id'] = $adeudo->id;
                    $input['pago_id'] = $adeudo->caja->pago->id;
                    $input['caja_id'] = $adeudo->caja->id;
                    $input['subtotal'] = $adeudo->caja->subtotal;
                    $input['descuento'] = $adeudo->caja->descuento;
                    $input['recargo'] = $adeudo->caja->recargo;
                    $input['total'] = $adeudo->caja->total;
                    $input['cliente_id'] = $adeudo->cliente_id;
                    $input['plantel_id'] = $adeudo->cliente->plantel_id;
                    $input['usu_alta_id'] = 1;
                    $input['usu_mod_id'] = 1;
                    AdeudoPagoOnLine::create($input);
                }
            } else {

                if (is_null($adeudo_pago_online)) {
                    $input['matricula'] = $adeudo->cliente->matricula;
                    $input['adeudo_id'] = $adeudo->id;
                    $input['pago_id'] = 0;
                    $input['caja_id'] = 0;
                    $datos_calculados = $this->predefinido($adeudo->id);
                    //dd($datos_calculados);
                    $input['subtotal'] = $datos_calculados['subtotal'];
                    $input['descuento'] = $datos_calculados['descuento'];
                    $input['recargo'] = $datos_calculados['recargo'];
                    $input['total'] = $datos_calculados['total'];
                    $input['fecha_limite'] = $datos_calculados['fecha_limite'];
                    $input['cliente_id'] = $adeudo->cliente_id;
                    $input['plantel_id'] = $adeudo->cliente->plantel_id;
                    $input['usu_alta_id'] = 1;
                    $input['usu_mod_id'] = 1;
                    AdeudoPagoOnLine::create($input);
                } else {
                    $hoy = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
                    //dd($hoy->toDateString());
                    //dd($hoy->toDateString() != $adeudo_pago_online->created_at->toDateString());
                    if ($hoy->toDateString() != $adeudo_pago_online->created_at->toDateString()) {
                        //$input['matricula'] = $adeudo->cliente->matricula;
                        //$input['cliente_id'] = $adeudo->cliente->id;
                        //$input['adeudo_id'] = $adeudo->id;
                        //dd($adeudo);
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
                    }
                }
            }
        }
    }

    public function predefinido($adeudo_tomado)
    {
        $adeudo = Adeudo::with('planPagoLn')->find($adeudo_tomado);
        //dd($conceptosValidos);

        $adeudos = Adeudo::where('id', '=', $adeudo_tomado)->get();
        //dd($adeudos);

        $cliente = Cliente::with('autorizacionBecas')->find($adeudo->cliente_id);
        //dd($adeudos->toArray());

        foreach ($adeudos as $adeudo) {
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
                    if (
                        $beca->lectivo->inicio <= $adeudo->fecha_pago and
                        $beca->lectivo->fin >= $adeudo->fecha_pago and
                        $beca->aut_dueno == 4 and
                        is_null($beca->deleted_at)
                    ) {
                        $beca_a = $beca->id;
                        //dd($beca);
                    }
                }

                $beca_autorizada = AutorizacionBeca::find($beca_a);
                //                        dd($beca_autorizada->monto_mensualidad > 0);
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
                        if ($fecha_caja >= $fecha_adeudo) {

                            $dias = $fecha_caja->diffInDays($fecha_adeudo);
                            if ($fecha_caja < $fecha_adeudo) {
                                $dias = $dias * -1;
                            }
                            //dd($dias);

                            //calcula recargo o descuento segun regla y aplica
                            if ($dias >= $regla->dia_inicio and $dias <= $regla->dia_fin) {
                                //dd($fecha_adeudo);
                                if ($regla->dia_fin > 60) {
                                    $caja_ln['fecha_limite'] = $fecha_adeudo->addDay(60)->toDateString();
                                } else {
                                    $caja_ln['fecha_limite'] = $fecha_adeudo->addDay($regla->dia_fin - 1)->toDateString();
                                }

                                if ($regla->tipo_regla_id == 1) {
                                    //dd($regla->porcentaje);

                                    if ($regla->porcentaje > 0) {
                                        //dd($regla->porcentaje);
                                        $regla_recargo = $caja_ln['total'] * $regla->porcentaje;
                                        $caja_ln['recargo'] = $caja_ln['recargo'] + $regla_recargo;
                                        //$caja_ln['recargo'] = $adeudo->monto * $regla->porcentaje;
                                        //echo $caja_ln['recargo'];
                                    } else {
                                        if ($adeudo->bnd_eximir_descuento_regla == 0) {
                                            $regla_descuento = $caja_ln['total'] * $regla->porcentaje * -1;
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
                                            $regla_descuento = $caja_ln['total'] * $regla->porcentaje * -1;
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
                if ($adeudo->fecha_pago > date('Y-m-d')) {
                    $caja_ln['fecha_limite'] = $adeudo->fecha_pago;
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
    }

    public function verDetalle(Request $request)
    {
        $datos = $request->all();
        $adeudo_pago_online = AdeudoPagoOnLine::find($datos['adeudo_pago_online_id']);
        $plantel = Plantel::find($adeudo_pago_online->adeudo->cliente->plantel_id);
        $forma_pagos = $plantel->formaPagos()->whereNull('forma_pagos.cve_multipagos')->pluck('name', 'id');

        return view('fichaPagos.detalle', compact('adeudo_pago_online', 'forma_pagos'));
    }

    public function crearCajaPagoPeticion(Request $request)
    {
        $datos = $request->all();
        //dd($datos);
        $adeudo_pago_online = AdeudoPagoOnLine::with('cliente')
            ->with('caja')
            ->with('pago')
            ->with('peticionMultipagos')
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
            $consecutivo = $plantel->consecutivo++;
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

            $consecutivo = $plantel->consecutivo_pago++;
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
            $datosMultipagos['mp_account'] = 6683;
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
            $datosMultipagos['mp_datereference'] = $adeudo_pago_online->fecha_limite;

            $peticion_multipagos = PeticionMultipago::create($datosMultipagos);

            //Se actualizan los datos en el registro de pagos en linea
            $adeudo_pago_online->peticion_multipago_id = $peticion_multipagos->id;
            $adeudo_pago_online->save();
        } else {
            $adeudo_pago_online = $adeudo_pago_online->peticionMultipago;
            //PeticionMultipago::find($adeudo_pago_online->peticion_multipago_id);

            $datosMultipagos['mp_account'] = 6683;
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
            $datosMultipagos['mp_datereference'] = $adeudo_pago_online->fecha_limite;

            $adeudo_pago_online->update($datosMultipagos);
        }

        return response()->json([
            'datos' => $datosMultipagos,
        ], 200);
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
                ->where('ce.id', '>', '0')
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
        $crearRegistro['mp_paymentmethod'] = $datos['mp_paymentmethod'];
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
                SuccessMultipago::create($crearRegistro);
            }

            $peticion = PeticionMultipago::where('mp_order', $crearRegistro['mp_order'])
                ->where('mp_reference', $crearRegistro['mp_reference'])
                ->where('mp_amount', $crearRegistro['mp_amount'])
                ->first();
            $pago = Pago::find($peticion->pago_id);

            //dd($peticion->toArray());
            if ($datos['mp_response'] == '00') {
                //$pago = Pago::find($peticion->pago_id);
                $pago->bnd_pagado = 1;
                $pago->save();
                $caja = $pago->caja;
                $caja->st_caja_id = 1;
                $caja->save();
            }

            return redirect()->route('fichaAdeudos.index');
        } else {
            dd('Firma incorrecta');
        }
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
        $input['toke_unico'] = uniqid(base64_encode(str_random(6)));
        $input['usu_alta_id'] = Auth::user()->id;
        $input['usu_mod_id'] = Auth::user()->id;
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
}
