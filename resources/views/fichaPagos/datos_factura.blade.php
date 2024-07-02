@extends('layouts.master1')

@section('content')

<style type="text/css">
input{
    text-transform: uppercase;
}
</style>

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

    @php
        $fact_40_activa=\App\Param::where('llave', 'fact_40_activa')->first();

    @endphp

    @if($fact_40_activa->valor==0)
    {!! Form::model($cliente, array('route' => array('fichaAdeudos.confirmarFactura', $adeudo_pago_on_line),'method' => 'post','id'=>'frm')) !!}
    @elseif($fact_40_activa->valor==1)
    {!! Form::model($cliente, array('route' => array('fichaAdeudos.confirmarFactura40', $adeudo_pago_on_line),'method' => 'post','id'=>'frm')) !!}
    @endif

            <div class="form-group col-md-4 @if($errors->has('curp')) has-error @endif">
                <label for="curp-field">*CURP del Alumno</label>
                <input type="hidden" name="_token" id="_token"  value="<?= csrf_token(); ?>">
                {!! Form::text("curp", null, array("class" => "form-control input-sm", "id" => "curp-field", 'onkeyup'=>"javascript:this.value=this.value.toUpperCase();")) !!}
                @if($errors->has("curp"))
                <span class="help-block">{{ $errors->first("curp") }}</span>
                @endif
            </div>
            <div class="row"></div>
            <div class="form-group col-md-4 @if($errors->has('tipo_persona_id')) has-error @endif">
                <label for="tipo_persona_id-field">*Tipo Persona</label>
                {!! Form::select("tipo_persona_id", $tipoPersonas, null, array("class" => "form-control select_seguridad", "id" => "tipo_persona_id-field", 'style'=>'width:100%')) !!}
                <div id='loading' style='display: none'><img src="{{ asset('images/ajax-loader.gif') }}" title="...Enviando" /></div>
                @if($errors->has("tipo_persona_id"))
                <span class="help-block">{{ $errors->first("tipo_persona_id") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('uso_factura_id')) has-error @endif">
                <label for="uso_factura_id-field">*Uso Factura</label>
                {!! Form::select("uso_factura_id", $usoFactura, null, array("class" => "form-control select_seguridad", "id" => "uso_factura_id-field", 'style'=>'width:100%')) !!}
                @if($errors->has("uso_factura_id"))
                <span class="help-block">{{ $errors->first("uso_factura_id") }}</span>
                @endif
            </div>

            <div class="form-group col-md-4 @if($errors->has('frazon')) has-error @endif">
                <label for="frazon-field">*Nombre o Razon Social</label>
                {!! Form::text("frazon", null, array("class" => "form-control input-sm", "id" => "frazon-field")) !!}
                @if($errors->has("frazon"))
                <span class="help-block">{{ $errors->first("frazon") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('frfc')) has-error @endif" style="clear:left;">
                <label for="frfc-field">*RFC</label>
                {!! Form::text("frfc", null, array("class" => "form-control input-sm", "id" => "frfc-field", 'onkeyup'=>"javascript:this.value=this.value.toUpperCase();")) !!}
                @if($errors->has("frfc"))
                <span class="help-block">{{ $errors->first("frfc") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fmail')) has-error @endif"  >
                <label for="fmail-field">*Correo Electronico</label>
                {!! Form::text("fmail", null, array("class" => "form-control input-sm", "id" => "fmail-field")) !!}
                @if($errors->has("fmail"))
                <span class="help-block">{{ $errors->first("fmail") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fcalle')) has-error @endif" >
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
            <div class="form-group col-md-4 @if($errors->has('regimen_fiscal_id')) has-error @endif">
                <label for="regimen_fiscal_id-field">*Regimen Fiscal</label>
                {!! Form::select("regimen_fiscal_id", $regimenFiscal, null, array("class" => "form-control select_seguridad", "id" => "regimen_fiscal_id-field", 'style'=>'width:100%')) !!}
                @if($errors->has("regimen_fiscal_id"))
                <span class="help-block">{{ $errors->first("regimen_fiscal_id") }}</span>
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

    cmbUsoFactura();

    $("#tipo_persona_id-field").change(function() {
        cmbUsoFactura();
   });

    function cmbUsoFactura(){
        $.ajax({
                  url: '{{ route("fichaAdeudos.cmbUsoFactura") }}',
                  type: 'GET',
                  data: {
                      'tipo_persona_id':$('#tipo_persona_id-field option:selected').val(),
                      'uso_factura_id':$('#uso_factura_id-field option:selected').val(),
                  },
                  dataType: 'json',
                  beforeSend : function(){$("#loading").show();},
                  complete : function(){$("#loading").hide();},
                  success: function(data){
                      //$example.select2("destroy");
                      $('#uso_factura_id-field').html('');

                      //$('#especialidad_id-field').empty();
                      $('#uso_factura_id-field').append($('<option></option>').text('Seleccionar').val('0'));

                      $.each(data, function(i) {
                          //alert(data[i].name);
                          $('#uso_factura_id-field').append("<option "+data[i].selectec+" value=\""+data[i].id+"\">"+data[i].name+"<\/option>");
                      });
                  }
              });
    }

    $("#bootbox-confirm").on(ace.click_event, function(e) {
        e.preventDefault();
        let tipo_persona=$("#tipo_persona_id-field option:selected").text();
        let uso_factura=$("#uso_factura_id-field option:selected").text();
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
        let mail=$("#fmail-field").val();
        //console.log(forma_pago);

        //console.log(pagador);
        bootbox.confirm({
            message: "<h3>Confirmar datos de facturacion:</h3> "+
                    "<strong>Tipo de Persona:</strong> "+tipo_persona+"<br>"+
                    "<strong>Uso Factura:</strong> "+uso_factura+"<br>"+
                    "<strong>Nombre o Razón Social:</strong> "+razon+"<br>"+
                    "<strong>RFC:</strong> "+rfc+"<br>"+
                    "Correo Electrónico: "+mail+"<br>"+
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
                    //$('#frm_multipagos').attr("action", url_metodo);
                    console.log($('#frm'));
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
