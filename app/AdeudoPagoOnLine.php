<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdeudoPagoOnLine extends Model
{

    use SoftDeletes;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    //Mass Assignment
    protected $fillable = ['matricula', 'adeudo_id', 'pago_id', 'caja_id', 'caja_ln_id', 'peticion_multipago_id', 'subtotal', 'descuento', 'recargo', 'total', 'cliente_id', 'plantel_id', 'usu_alta_id', 'usu_mod_id', 'fecha_limite'];

    public function usu_alta()
    {
        return $this->hasOne('App\User', 'id', 'usu_alta_id');
    } // end

    public function usu_mod()
    {
        return $this->hasOne('App\User', 'id', 'usu_mod_id');
    } // end


    protected $dates = ['deleted_at', 'fecha_limite'];

    public function cliente()
    {
        return $this->belongsTo('App\Cliente');
    }

    public function adeudo()
    {
        return $this->belongsTo('App\Adeudo');
    }

    public function caja()
    {
        return $this->belongsTo('App\Caja');
    }

    public function cajaLn()
    {
        return $this->belongsTo('App\CajaLn');
    }

    public function peticionMultipago()
    {
        return $this->belongsTo('App\PeticionMultipago');
    }

    public function pago()
    {
        return $this->belongsTo('App\Pago');
    }

    public function plantel()
    {
        return $this->belongsTo('App\Plantel');
    }
}
