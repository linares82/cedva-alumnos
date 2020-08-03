<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class PeticionMultipago extends Model
{

    use SoftDeletes;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    //Mass Assignment
    protected $fillable = ['pago_id', 'mp_account', 'mp_product', 'mp_order', 'mp_reference', 'mp_node', 'mp_concept', 'mp_amount', 'mp_customername', 'mp_currency', 'mp_signature', 'mp_urlsuccess', 'mp_urlfailure', 'contador_peticiones', 'usu_alta_id', 'usu_mod_id', 'mp_paymentmethod'];

    public function usu_alta()
    {
        return $this->hasOne('App\User', 'id', 'usu_alta_id');
    } // end

    public function usu_mod()
    {
        return $this->hasOne('App\User', 'id', 'usu_mod_id');
    } // end


    protected $dates = ['deleted_at'];

    // generated by relation command - Pago,PeticionMultipago - start
    public function pago()
    {
        return $this->belongsTo('App\Pago');
    } // end

    // generated by relation command - PeticionMultipago,ConciliacionMultiDetalle - start
    public function conciliacionMultiDetalles()
    {
        return $this->hasMany('App\ConciliacionMultiDetalle');
    } // end
}
