<?php

namespace App\Observers;

use Log;
use Auth;
use Exception;
use App\Pago;
use App\Param;
use App\BsBaja;
use App\Seguimiento;
use App\IngresoEgreso;

use App\CuentasEfectivo;
use App\valenceSdk\samples\BasicSample\UsoApi;

class PagoObserver
{
    /**
     * Listen to the User created event.
     *
     * @param  User  $user
     * @return void
     */
    public $pago;
    public function created(Pago $pago)
    {
        $this->pago = $pago;
        if ($this->pago->cuenta_efectivo_id > 0) {
            $cuentas_efectivo = CuentasEfectivo::where('id', $this->pago->cuenta_efectivo_id)->first();
            if (
                $cuentas_efectivo->saldo_inicial > 0 and
                $this->pago->fecha >= $cuentas_efectivo->fecha_saldo_inicial and
                $this->pago->bnd_pagado == 1 and
                $this->pago->bnd_referenciado <> 1
            ) {
                $cuentas_efectivo->saldo_actualizado = $cuentas_efectivo->saldo_actualizado + $this->pago->monto;
                $cuentas_efectivo->save();

                $ingreso = array();
                $ingreso['plantel_id'] = $this->pago->caja->plantel_id;
                $ingreso['cuenta_efectivo_id'] = $this->pago->cuenta_efectivo_id;
                $ingreso['pago_id'] = $this->pago->id;
                $ingreso['consecutivo_caja'] = $this->pago->caja->consecutivo;
                $ingreso['egreso_id'] = 0;
                $ingreso['concepto'] = "Pago";
                $ingreso['fecha'] = $this->pago->fecha;
                $ingreso['monto'] = $this->pago->monto;
                $ingreso['usu_alta_id'] = $this->pago->usu_alta_id;
                $ingreso['usu_mod_id'] = $this->pago->usu_mod_id;
                $ingreso['transference_id'] = 0;
                IngresoEgreso::create($ingreso);

                $cliente = $pago->caja->cliente;
                $seguimiento = $cliente->seguimiento;
                $cliente->st_cliente_id = 4;
                $cliente->save();
                $seguimiento->st_seguimiento_id = 2;
                $seguimiento->save();
            }
        }
    }

    public function updated(Pago $pago)
    {
        //dd("inicio");
        $this->pago = $pago;
        $ingreso_egreso = IngresoEgreso::where('pago_id', $this->pago->id)->first();
        //dd($ingreso_egreso);
        if ($this->pago->cuenta_efectivo_id > 0) {
            $cuentas_efectivo = CuentasEfectivo::where('id', $this->pago->cuenta_efectivo_id)->first();
            if (
                $cuentas_efectivo->saldo_inicial > 0 and
                $this->pago->fecha >= $cuentas_efectivo->fecha_saldo_inicial and
                $this->pago->bnd_pagado == 1 and
                $this->pago->bnd_referenciado == 1 and
                is_null($ingreso_egreso)
            ) {
                $cuentas_efectivo->saldo_actualizado = $cuentas_efectivo->saldo_actualizado + $this->pago->monto;
                $cuentas_efectivo->save();

                $ingreso = array();
                $ingreso['plantel_id'] = $this->pago->caja->plantel_id;
                $ingreso['cuenta_efectivo_id'] = $this->pago->cuenta_efectivo_id;
                $ingreso['pago_id'] = $this->pago->id;
                $ingreso['consecutivo_caja'] = $this->pago->caja->consecutivo;
                $ingreso['egreso_id'] = 0;
                $ingreso['concepto'] = "Pago";
                $ingreso['fecha'] = $this->pago->fecha;
                $ingreso['monto'] = $this->pago->monto;
                $ingreso['usu_alta_id'] = $this->pago->usu_alta_id;
                $ingreso['usu_mod_id'] = $this->pago->usu_mod_id;
                $ingreso['transference_id'] = 0;
                IngresoEgreso::create($ingreso);

                $cliente = $pago->caja->cliente;
                $seguimiento = $cliente->seguimiento;
                $cliente->st_cliente_id = 4;
                $cliente->save();
                $seguimiento->st_seguimiento_id = 2;
                $seguimiento->save();

                $param = Param::where('llave', 'apiVersion_bSpace')->first();
                $bs_activo = Param::where('llave', 'api_brightSpace_activa')->first();
                if ($bs_activo->valor == 1) {
                    try {
                        $apiBs = new UsoApi();

                        //dd($datos);
                        //Log::info('matricula bs reactivar en caja:'.$cliente->matricula);
                        $resultado = $apiBs->doValence2('GET', '/d2l/api/lp/' . $param->valor . '/users/?orgDefinedId=' . $cliente->matricula);
                        //Muestra resultado
                        $r = $resultado[0];
                        $datos = ['isActive' => True];
                        if (isset($r['UserId'])) {
                            $resultado2 = $apiBs->doValence2('PUT', '/d2l/api/lp/' . $param->valor . '/users/' . $r['UserId'] . '/activation', $datos);
                            $bsBaja = BsBaja::where('cliente_id', $cliente->id)
                                ->where('bnd_baja', 1)
                                ->whereNull('bnd_reactivar')
                                ->first();
                            //dd($bsBaja);
                            if (!is_null($bsBaja)) {
                                if (isset($resultado2['IsActive']) and $resultado2['IsActive'] and !is_null($bsBaja)) {
                                    $input['cliente_id'] = $cliente->id;
                                    $input['fecha_reactivar'] = Date('Y-m-d');
                                    $input['bnd_reactivar'] = 1;
                                    $input['usu_mod_id'] = Auth::user()->id;
                                    $bsBaja->update($input);
                                } else {
                                    $input['cliente_id'] = $cliente->id;
                                    $input['fecha_reactivar'] = Date('Y-m-d');
                                    $input['bnd_reactivar'] = 0;
                                    $input['usu_mod_id'] = Auth::user()->id;
                                    $bsBaja->update($input);
                                }
                            }
                        }
                        Log::info('pago actualizado Fil todo ok');
                    } catch (Exception $e) {
                        Log::info("cliente no encontrado en Brigth Space u otro error: " . $cliente->matricula . " - " . $e->getMessage());
                        //return false;
                    }
                }
            }
        }
    }

    public function deleting(Pago $pago)
    {
        $this->pago = $pago;
        if ($this->pago->cuenta_efectivo_id > 0) {
            $cuentas_efectivo = CuentasEfectivo::where('id', $this->pago->cuenta_efectivo_id)->first();
            if ($cuentas_efectivo->saldo_inicial > 0 and $this->pago->fecha >= $cuentas_efectivo->fecha_saldo_inicial) {
                $cuentas_efectivo->saldo_actualizado = $cuentas_efectivo->saldo_actualizado - $this->pago->monto;
                $cuentas_efectivo->save();

                $pago = IngresoEgreso::where('pago_id', $this->pago->id)->where('egreso_id', 0)->whereNull('deleted_at')->first();
                if (count($pago) > 0) {
                    $pago->delete();
                }
            }
        }
    }
}
