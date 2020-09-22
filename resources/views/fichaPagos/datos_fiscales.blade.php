@extends('layouts.master1')

@section('content')

<div class="row">
    <div class="col-md-12">
        <h4>Datos Para Facturar</h4>
        <span class="label label-danger label-white middle">Campos marcados con * son obligatorios.</span>
        <span class="label label-warning label-white middle">En caso de obtener un mensaje que impida su facturación, reportarlo a su sucursal.</span>
    </div>

    @if (session('error'))
    <div class="row">

    </div>
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">
            <i class="ace-icon fa fa-times"></i>
        </button>

        <strong>
            <i class="ace-icon fa fa-times"></i>
            Se presento el siguiente problema:
        </strong>

        {{ session('error') }}
        <br>
    </div>

    @endif

    {!! Form::model($cliente, array('route' => array('fichaAdeudos.confirmarDatosFiscales', $cliente->id),'method' => 'post','id'=>'frm')) !!}
            <div class="form-group col-md-4 @if($errors->has('curp')) has-error @endif">
                <label for="curp-field">*CURP del Alumno</label>
                {!! Form::text("curp", null, array("class" => "form-control input-sm", "id" => "curp-field", 'onkeyup'=>"javascript:this.value=this.value.toUpperCase();")) !!}
                @if($errors->has("curp"))
                <span class="help-block">{{ $errors->first("curp") }}</span>
                @endif
            </div>
            <div class="row"></div>
            <div class="col-md-12"><label>Datos Fiscales</label></div>
            <div class="form-group col-md-4 @if($errors->has('tipo_persona_id')) has-error @endif">
                <label for="tipo_persona_id-field">*Tipo Persona</label>
                {!! Form::select("tipo_persona_id", $tipoPersonas, null, array("class" => "form-control select_seguridad", "id" => "tipo_persona_id-field", 'style'=>'width:100%')) !!}
                @if($errors->has("tipo_persona_id"))
                <span class="help-block">{{ $errors->first("tipo_persona_id") }}</span>
                @endif
            </div>

            <div class="form-group col-md-4 @if($errors->has('frazon')) has-error @endif">
                <label for="frazon-field">*Nombre o Razon Social</label>
                {!! Form::text("frazon", null, array("class" => "form-control input-sm", "id" => "frazon-field")) !!}
                @if($errors->has("frazon"))
                <span class="help-block">{{ $errors->first("frazon") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('frfc')) has-error @endif">
                <label for="frfc-field">*RFC</label>
                {!! Form::text("frfc", null, array("class" => "form-control input-sm", "id" => "frfc-field", 'onkeyup'=>"javascript:this.value=this.value.toUpperCase();")) !!}
                @if($errors->has("frfc"))
                <span class="help-block">{{ $errors->first("frfc") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fcalle')) has-error @endif" style="clear:left;">
                <label for="fcalle-field">*Calle</label>
                {!! Form::text("fcalle", null, array("class" => "form-control input-sm", "id" => "fcalle-field")) !!}
                @if($errors->has("fcalle"))
                <span class="help-block">{{ $errors->first("fcalle") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fno_exterior')) has-error @endif">
                <label for="fno_exterior-field">*No. Exterior</label>
                {!! Form::text("fno_exterior", null, array("class" => "form-control input-sm", "id" => "fno_exterior-field")) !!}
                @if($errors->has("fno_exterior"))
                <span class="help-block">{{ $errors->first("fno_exterior") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fno_interior')) has-error @endif">
                <label for="fno_interior-field">No. Interior</label>
                {!! Form::text("fno_interior", null, array("class" => "form-control input-sm", "id" => "fno_interior-field")) !!}
                @if($errors->has("fno_interior"))
                <span class="help-block">{{ $errors->first("fno_interior") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fcolonia')) has-error @endif">
                <label for="fcolonia-field">*Colonia</label>
                {!! Form::text("fcolonia", null, array("class" => "form-control input-sm", "id" => "fcolonia-field")) !!}
                @if($errors->has("fcolonia"))
                <span class="help-block">{{ $errors->first("fcolonia") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fciudad')) has-error @endif">
                <label for="fciudad-field">Ciudad</label>
                {!! Form::text("fciudad", null, array("class" => "form-control input-sm", "id" => "fciudad-field")) !!}
                @if($errors->has("fciudad"))
                <span class="help-block">{{ $errors->first("fciudad") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fmunicipio')) has-error @endif">
                <label for="fmunicipio-field">*Municipio</label>
                {!! Form::text("fmunicipio", null, array("class" => "form-control input-sm", "id" => "fmunicipio-field")) !!}
                @if($errors->has("fmunicipio"))
                <span class="help-block">{{ $errors->first("fmunicipio") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('festado')) has-error @endif">
                <label for="festado-field">*Estado</label>
                {!! Form::text("festado", null, array("class" => "form-control input-sm", "id" => "festado-field")) !!}
                @if($errors->has("festado"))
                <span class="help-block">{{ $errors->first("festado") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fpais')) has-error @endif">
                <label for="fpais-field">*Pais</label>
                {!! Form::text("fpais", null, array("class" => "form-control input-sm", "id" => "fpais-field")) !!}
                @if($errors->has("fpais"))
                <span class="help-block">{{ $errors->first("fpais") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fcp')) has-error @endif">
                <label for="fcp-field">*C.P.</label>
                {!! Form::text("fcp", null, array("class" => "form-control input-sm", "id" => "fcp-field")) !!}
                @if($errors->has("fcp"))
                <span class="help-block">{{ $errors->first("fcp") }}</span>
                @endif
            </div>

            <div class="row">
            </div>

            <div class="well well-sm">
                <button type="submit" class="btn btn-primary" id="bootbox-confirm">Guardar</button>
                <a class="btn btn-link pull-right" href="{{ route('fichaAdeudos.index') }}"><i class="glyphicon glyphicon-backward"></i>  Regresar</a>
            </div>

            {!! Form::close() !!}
</div>
@endsection
@push('scripts')
<script type="text/javascript">
$(document).ready(function(){
    $("#bootbox-confirm").on(ace.click_event, function(e) {
        e.preventDefault();
        let tipo_persona=$("#tipo_persona_id-field option:selected").text();
        let razon=$("#frazon-field").val();
        let rfc=$("#frfc-field").val();
        let calle=$("#fcalle-field").val();
        let no_exterior=$("#fno_exterior-field").val();
        let no_interior=$("#fno_interior-field").val();
        let colonia=$("#fcolonia-field").val();
        let ciudad=$("#fciudad-field").val();
        let municipio=$("#fmunicipio-field").val();
        let estado=$("#festado-field").val();
        let pais=$("#fpais-field").val();
        let cp=$("#fcp-field").val();
        //console.log(forma_pago);

        //console.log(pagador);
        bootbox.confirm({
            message: "<h3>Confirmar datos de facturacion:</h3> "+
                    "<strong>Tipo de Persona:</strong> "+tipo_persona+"<br>"+
                    "<strong>Nombre o Razón Social:</strong> "+razon+"<br>"+
                    "<strong>RFC:</strong> "+rfc+"<br>"+
                    "<strong>Calle:</strong> "+calle+"<br>"+
                    "No. Interior: "+no_interior+"<br>"+
                    "No. Exterior: "+no_exterior+"<br>"+
                    "Colonia: "+colonia+"<br>"+
                    "Ciudad: "+ciudad+"<br>"+
                    "Municipio: "+municipio+"<br>"+
                    "Estado: "+estado+"<br>"+
                    "País: "+pais+"<br>"+
                    "CP: "+cp+"<br>",
            buttons: {
                confirm: {
                    label: "Facturar",
                    className: "btn-primary btn-sm",
                },
                cancel: {
                    label: "Cancelar",
                    className: "btn-sm",
                }
            },
            callback: function(result) {
                if(result){
                    //$('#frm_multipagos').attr("action", data.datos.url_peticion);
                    $('#frm').submit();
                }

            }

        });
    });
});

function formatoFecha(texto){
  return texto.replace(/^(\d{4})-(\d{2})-(\d{2})$/g,'$1/$2/$3');
}

</script>
@endpush
