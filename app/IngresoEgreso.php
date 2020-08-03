<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IngresoEgreso extends Model
{
    use SoftDeletes;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    //Mass Assignment
    protected $fillable = [
        'plantel_id', 'cuenta_efectivo_id', 'pago_id', 'consecutivo_caja', 'egreso_id',
        'concepto', 'fecha', 'monto', 'usu_alta_id', 'usu_mod_id', 'transference_id'
    ];

    public function usu_alta()
    {
        return $this->hasOne('App\User', 'id', 'usu_alta_id');
    } // end

    public function usu_mod()
    {
        return $this->hasOne('App\User', 'id', 'usu_mod_id');
    } // end


    protected $dates = ['deleted_at'];
}
