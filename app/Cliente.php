<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GetAllDataTrait;
use App\Traits\RelationManagerTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Events\ClienteCreated;
use App\Events\ClienteUpdating;

class Cliente extends Model
{
    //use RelationManagerTrait, GetAllDataTrait;
    use SoftDeletes;

    protected $events = [
        'created' => ClienteCreated::class,
        'updating' => ClienteUpdating::class,
    ];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    //Mass Assignment
    protected $fillable = [
        'cve_cliente', 'nombre', 'nombre2', 'ape_paterno', 'ape_materno', 'fec_registro',
        'tel_fijo', 'tel_cel', 'mail', 'calle', 'no_exterior', 'no_interior', 'colonia', 'cp',
        'municipio_id', 'estado_id', 'st_cliente_id', 'especialidad_id', 'ofertum_id', 'medio_id',
        'expo', 'otro_medio', 'empleado_id', 'promociones', 'promo_cel', 'promo_correo',
        'plantel_id', 'nivel_id', 'grado_id', 'curso_id', 'subcurso_id', 'diplomado_id',
        'subdiplomado_id', 'otro_id', 'subotro_id', 'usu_alta_id', 'usu_mod_id', 'matricula',
        'celular_confirmado', 'correo_confirmado', 'especialidad2_id', 'especialidad3_id',
        'especialidad4_id', 'cve_alumno', 'genero', 'curp', 'fec_nacimiento', 'lugar_nacimiento',
        'extranjero', 'distancia_escuela', 'peso', 'estatura', 'tipo_sangre', 'alergias',
        'medicinas_contraindicadas', 'color_piel', 'color_cabello', 'senas_particulares',
        'nombre_padre', 'curp_padre', 'dir_padre', 'tel_padre', 'cel_padre', 'tel_ofi_padre',
        'mail_padre', 'nombre_madre', 'curp_madre', 'dir_madre', 'tel_madre', 'cel_madre',
        'tel_ofi_madre', 'mail_madre', 'nombre_acudiente', 'curp_acudiente', 'dir_acudiente',
        'tel_acudiente', 'cel_acudiente', 'tel_ofi_acudiente', 'mail_acudiente', 'empresa_id',
        'turno_id', 'turno2_id', 'turno3_id', 'turno4_id', 'escuela_procedencia', 'ciclo_id',
        'ccuestionario_id', 'contador_sms', 'contador_mail', 'tpo_informe_id',
        'segmento_mercado_id', 'beca_bnd', 'beca_porcentaje', 'beca_nota', 'paise_id', 'monto_mensualidad',
        'justificacion_beca', 'bnd_regingreso', 'escolaridad_id', 'interes_estudio_id',
        'no_poliza', 'nacionalidad', 'edad', 'estado_civil_id', 'estado_nacimiento_id', 'fec_reingreso',
        'pagador_id', 'discapacidad_id', 'bnd_trabaja', 'bnd_indigena', 'tipo_persona_id', 'frfc', 'frazon',
        'fcalle', 'fno_exterior', 'fno_interior', 'fcolonia', 'fciudad', 'fmunicipio', 'festado', 'fpais', 'fcp'
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

    // generated by relation command - StCliente,Cliente - start
    public function stCliente()
    {
        return $this->belongsTo('App\StCliente');
    } // end

    // generated by relation command - Medio,Cliente - start
    public function medio()
    {
        return $this->belongsTo('App\Medio');
    } // end

    // generated by relation command - Estado,Cliente - start
    public function estado()
    {
        return $this->belongsTo('App\Estado');
    } // end

    // generated by relation command - Municipio,Cliente - start
    public function municipio()
    {
        return $this->belongsTo('App\Municipio');
    } // end

    public function oferta()
    {
        return $this->belongsTo('App\Ofertum', 'ofertum_id', 'id');
    } // end

    public function nivel()
    {
        return $this->belongsTo('App\Nivel');
    } // end
    public function grado()
    {
        return $this->belongsTo('App\Grado');
    } // end
    public function diplomado()
    {
        return $this->belongsTo('App\Diplomado');
    } // end
    public function subdiplomado()
    {
        return $this->belongsTo('App\subdiplomado');
    } // end
    public function curso()
    {
        return $this->belongsTo('App\Curso');
    } // end
    public function subcurso()
    {
        return $this->belongsTo('App\Subcurso');
    } // end
    public function otro()
    {
        return $this->belongsTo('App\Otro');
    } // end
    public function subotro()
    {
        return $this->belongsTo('App\Subotro');
    } // end
    public function empleado()
    {
        return $this->belongsTo('App\Empleado');
    } // end
    public function plantel()
    {
        return $this->belongsTo('App\Plantel');
    } // end
    public function turno()
    {
        return $this->belongsTo('App\Turno');
    } // end
    public function ciclo()
    {
        return $this->belongsTo('App\Ciclo');
    } // end
    public function tpoInforme()
    {
        return $this->belongsTo('App\TpoInforme');
    } // end
    public function seguimiento()
    {
        return $this->hasOne('App\Seguimiento', 'cliente_id', 'id');
    }
    /*
	public function preguntas()
    {
        return $this->hasMany('App\PreguntaCliente', 'cliente_id', 'id');
    }*/

    // generated by relation command - Especialidad,Cliente - start
    public function especialidad()
    {
        return $this->belongsTo('App\Especialidad');
    } // end

    public function empresa()
    {
        return $this->belongsTo('App\Empresa');
    } // end

    public function pivotDocCliente()
    {
        return $this->hasMany('App\PivotDocCliente');
    } // end

    public function inscripciones()
    {
        return $this->hasMany('App\Inscripcion');
    } // end

    //Scopes
    public function scopePlantel($query)
    {
        return $query->where('plantel_id', '=', Empleado::find(Auth::user()->id)->plantel_id);
    }

    // generated by relation command - Cliente,CombinacionCliente - start
    public function combinaciones()
    {
        return $this->hasMany('App\CombinacionCliente');
    } // end

    // generated by relation command - Cliente,CombinacionCliente - start
    public function combinacionClientes()
    {
        return $this->hasMany('App\CombinacionCliente');
    } // end

    // generated by relation command - Cliente,CcuestionarioDato - start
    public function ccuestionarioDatos()
    {
        return $this->hasMany('App\CcuestionarioDato');
    } // end

    // generated by relation command - Ccuestionario,Cliente - start
    public function ccuestionario()
    {
        return $this->belongsTo('App\Ccuestionario');
    } // end

    public function segmentoMercado()
    {
        return $this->belongsTo('App\SegmentoMercado');
    } // end

    // generated by relation command - Cliente,AsistenciaR - start
    public function asistenciaRs()
    {
        return $this->hasMany('App\AsistenciaR');
    } // end

    // generated by relation command - Cliente,Adeudo - start
    public function adeudos()
    {
        return $this->hasMany('App\Adeudo');
    } // end

    // generated by relation command - Cliente,Caja - start
    public function cajas()
    {
        return $this->hasMany('App\Caja');
    } // end

    // generated by relation command - Cliente,Caja - start
    public function asignacionAcademica()
    {
        return $this->hasMany('App\AsignacionAcademica');
    } // end

    // generated by relation command - Paise,Cliente - start
    public function paise()
    {
        return $this->belongsTo('App\Paise');
    } // end

    // generated by relation command - Cliente,AutorizacionBeca - start
    public function autorizacionBeca()
    {
        return $this->hasOne('App\AutorizacionBeca');
    } // end

    // generated by relation command - Cliente,HistoriaCliente - start
    public function historiaClientes()
    {
        return $this->hasMany('App\HistoriaCliente');
    } // end

    // generated by relation command - Cliente,Vinculacion - start
    public function vinculacions()
    {
        return $this->hasMany('App\Vinculacion');
    } // end

    public function interesEstudio()
    {
        return $this->belongsTo('App\InteresEstudio');
    } // end

    // generated by relation command - Cliente,MoodleBaja - start
    public function moodleBajas()
    {
        return $this->hasMany('App\MoodleBaja');
    } // end

    public function autorizacionBecas()
    {
        return $this->hasMany('App\AutorizacionBeca');
    } // end

    public function estadoCivil()
    {
        return $this->belongsTo('App\EstadoCivil');
    } // end

    // generated by relation command - Pagador,Cliente - start
    public function escolaridad()
    {
        return $this->belongsTo('App\Escolaridad');
    } // end

    // generated by relation command - Pagador,Cliente - start
    public function pagador()
    {
        return $this->belongsTo('App\Pagador');
    } // end

    public function discapacidad()
    {
        return $this->belongsTo('App\Discapacidad');
    } // end

    public function tipoPersona()
    {
        return $this->belongsTo('App\TipoPersona');
    } // end
}
