<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grado extends Model
{

    use SoftDeletes;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    //Mass Assignment
    protected $fillable = [
        'nivel_id', 'name', 'especialidad_id', 'plantel_id', 'usu_alta_id', 'usu_mod_id', 'cct', 'seccion',
        'precio_online', 'mexico_bnd', 'nombre2', 'modulo_final_id', 'rvoe', 'denominacion', 'fec_rvoe', 'id_mapa',
        'clave_servicio', 'nivel_educativo_sat_id'
    ];

    public function usu_alta()
    {
        return $this->hasOne('App\User', 'id', 'usu_alta_id');
    } // end

    public function usu_mod()
    {
        return $this->hasOne('App\User', 'id', 'usu_mod_id');
    } // end

    public function nivel()
    {
        return $this->belongsTo('App\Nivel');
    } // end
    public function plantel()
    {
        return $this->belongsTo('App\Plantel');
    } // end
    public function especialidad()
    {
        return $this->belongsTo('App\Especialidad');
    } // end
    protected $dates = ['deleted_at'];

    // generated by relation command - Grado,Alumno - start
    public function alumnos()
    {
        return $this->hasMany('App\Alumno');
    } // end

    // generated by relation command - Grado,PeriodoEstudio - start
    public function periodoEstudios()
    {
        return $this->hasMany('App\PeriodoEstudio');
    } // end

    // generated by relation command - Grado,Hacademica - start
    public function hacademicas()
    {
        return $this->hasMany('App\Hacademica');
    } // end

    // generated by relation command - Grado,CombinacionCliente - start
    public function combinacionClientes()
    {
        return $this->hasMany('App\CombinacionCliente');
    } // end
    public function nivelEducativoSat()
    {
        return $this->belongsTo('App\NivelEducativoSat');
    } // end
}
