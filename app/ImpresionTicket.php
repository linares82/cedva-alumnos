<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImpresionTicket extends Model
{
    use SoftDeletes;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    //Mass Assignment
    protected $fillable = [
        'caja_id', 'pago_id', 'cliente_id', 'plantel_id', 'consecutivo', 'monto',
        'toke_unico', 'usu_alta_id', 'usu_mod_id', 'fecha_pago'
    ];

    public function usu_alta()
    {
        return $this->hasOne('App\User', 'id', 'usu_alta_id');
    } // end

    public function usu_mod()
    {
        return $this->hasOne('App\User', 'id', 'usu_mod_id');
    } // end

    public function plantel()
    {
        return $this->belongsTo('App\Plantel', 'plantel_id', 'id');
    } // end

    public function caja()
    {
        return $this->belongsTo('App\Caja', 'caja_id', 'id');
    } // end

    public function cliente()
    {
        return $this->belongsTo('App\Cliente', 'cliente_id', 'id');
    } // end

    public function pago()
    {
        return $this->belongsTo('App\Pago', 'pago_id', 'id');
    } // end

    protected $dates = ['deleted_at'];
}
