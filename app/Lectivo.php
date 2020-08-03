<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Lectivo extends Model
{

    use SoftDeletes;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    //Mass Assignment
    protected $fillable = ['name', 'activo', 'bachillerato_bnd', 'carrera_bnd', 'usu_alta_id', 'usu_mod_id', 'inicio', 'fin', 'grafica_bnd', 'desc_certificado'];

    protected $dates = ['deleted_at'];

    public function usu_alta()
    {
        return $this->hasOne('App\User', 'id', 'usu_alta_id');
    }

    // end

    public function usu_mod()
    {
        return $this->hasOne('App\User', 'id', 'usu_mod_id');
    }

    // generated by relation command - Lectivo,Plantel - start
    public function plantels()
    {
        return $this->hasMany('App\Plantel');
    } // end

    // generated by relation command - Lectivo,Horario - start
    public function horarios()
    {
        return $this->hasMany('App\Horario');
    } // end

    // generated by relation command - Lectivo,Hacademica - start
    public function hacademicas()
    {
        return $this->hasMany('App\Hacademica');
    } // end

    // generated by relation command - Lectivo,CalendarioEvaluacion - start
    public function calendarioEvaluacions()
    {
        return $this->hasMany('App\CalendarioEvaluacion');
    } // end

    public function periodoExamens()
    {
        return $this->hasMany('App\PeriodoExamen');
    } // end


}
