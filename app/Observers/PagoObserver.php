<?php

namespace App\Observers;

use App\Pago;
use App\CuentasEfectivo;
use App\IngresoEgreso;

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
            }
        }
    }

    public function updating(Pago $pago)
    {
        $this->pago = $pago;
        $ingreso_egreso = IngresoEgreso::where('pago_id', $this->pago->id)->first();
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
