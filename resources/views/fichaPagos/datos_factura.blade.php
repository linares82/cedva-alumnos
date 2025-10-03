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
    <form action="{{route('fichaAdeudos.confirmarFactura', $adeudo_pago_on_line)}}" id="frm" method="POST">
    @elseif($fact_40_activa->valor==1)
    <form action="{{route('fichaAdeudos.confirmarFactura40', $adeudo_pago_on_line)}}" id="frm" method="POST">
    @endif

            {{ csrf_field() }}
        <div class="form-group col-md-4 @if($errors->has('curp')) has-error @endif">
                <label for="curp">*CURP del Alumno</label>
                <input type="text" value="{{ $cliente->curp }}" name="curp" id="curp-field" class="form-control input-sm" onkeyup="javascript:this.value=this.value.toUpperCase();">
                @if($errors->has("curp"))
                <span class="help-block">{{ $errors->first("curp") }}</span>
                @endif
            </div>
            <div class="row"></div>
            <div class="col-md-12"><label>Datos Fiscales</label></div>
            <div class="form-group col-md-4 @if($errors->has('tipo_persona_id')) has-error @endif">
                <label for="tipo_persona_id">*Tipo Persona</label>
                <select class="form-control" id="tipo_persona_id-field" name="tipo_persona_id" >
                        <option value="" style="display: none;" {{ old('tipo_persona_id', optional($cliente)->tipo_persona_id ?: '') == '' ? 'selected' : '' }} disabled selected> Seleccionar opción </option>
                    @foreach ($tipoPersonas as $key => $tipoPersonaLabel)
                        <option value="{{ $key }}" {{ old('tipo_persona_id', optional($cliente)->tipo_persona_id) == $key ? 'selected' : '' }}>
                            {{ $tipoPersonaLabel }}
                        </option>
                    @endforeach
                </select>
                <div id='loading' style='display: none'><img src="{{ asset('images/ajax-loader.gif') }}" title="...Enviando" /></div>
                @if($errors->has("tipo_persona_id"))
                <span class="help-block">{{ $errors->first("tipo_persona_id") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('uso_factura_id')) has-error @endif">
                <label for="uso_factura_id">*Uso Factura</label>

                <select class="form-control" id="uso_factura_id-field" name="uso_factura_id" >
                        <option value="" style="display: none;" {{ old('uso_factura_id', optional($cliente)->uso_factura_id ?: '') == '' ? 'selected' : '' }} disabled selected> Seleccionar opción </option>
                    @foreach ($usoFactura as $key => $usoFacturaLabel)
                        <option value="{{ $key }}" {{ old('uso_factura_id', optional($cliente)->uso_factura_id) == $key ? 'selected' : '' }}>
                            {{ $usoFacturaLabel }}
                        </option>
                    @endforeach
                </select>

                @if($errors->has("uso_factura_id"))
                <span class="help-block">{{ $errors->first("uso_factura_id") }}</span>
                @endif
            </div>

            <div class="form-group col-md-4 @if($errors->has('frazon')) has-error @endif">
                <label for="frazon">*Nombre o Razon Social</label>
                <input type="text" value="{{ $cliente->frazon }}" name="frazon" id="frazon-field" class="form-control input-sm">
                @if($errors->has("frazon"))
                <span class="help-block">{{ $errors->first("frazon") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('frfc')) has-error @endif" style="clear:left;">
                <label for="frfc">*RFC</label>
                <input type="text" value="{{ $cliente->frfc }}" name="frfc" id="frfc-field" class="form-control input-sm">
                @if($errors->has("frfc"))
                <span class="help-block">{{ $errors->first("frfc") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fmail')) has-error @endif" >
                <label for="fmail">*Correo Electronico</label>
                <input type="text" value="{{ $cliente->fmail }}" name="fmail" id="fmail-field" class="form-control input-sm">
                @if($errors->has("fmail"))
                <span class="help-block">{{ $errors->first("fmail") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fcalle')) has-error @endif">
                <label for="fcalle">*Calle</label>
                <input type="text" value="{{ $cliente->fcalle }}" name="fcalle" id="fcalle-field" class="form-control input-sm">
                @if($errors->has("fcalle"))
                <span class="help-block">{{ $errors->first("fcalle") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fno_exterior')) has-error @endif">
                <label for="fno_exterior">*No. Exterior</label>
                <input type="text" value="{{ $cliente->fno_exterior }}" name="fno_exterior" id="fno_exterior-field" class="form-control input-sm">
                @if($errors->has("fno_exterior"))
                <span class="help-block">{{ $errors->first("fno_exterior") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fno_interior')) has-error @endif">
                <label for="fno_interior">No. Interior</label>
                <input type="text" value="{{ $cliente->fno_interior }}" name="fno_interior" id="fno_interior-field" class="form-control input-sm">
                @if($errors->has("fno_interior"))
                <span class="help-block">{{ $errors->first("fno_interior") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fcolonia')) has-error @endif">
                <label for="fcolonia">*Colonia</label>
                <input type="text" value="{{ $cliente->fcolonia }}" name="fcolonia" id="fcolonia-field" class="form-control input-sm">
                @if($errors->has("fcolonia"))
                <span class="help-block">{{ $errors->first("fcolonia") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fciudad')) has-error @endif">
                <label for="fciudad">Ciudad</label>
                <input type="text" value="{{ $cliente->fciudad }}" name="fciudad" id="fciudad-field" class="form-control input-sm">
                @if($errors->has("fciudad"))
                <span class="help-block">{{ $errors->first("fciudad") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fmunicipio')) has-error @endif">
                <label for="fmunicipio">*Municipio</label>
                <input type="text" value="{{ $cliente->fmunicipio }}" name="fmunicipio" id="fmunicipio-field" class="form-control input-sm">
                @if($errors->has("fmunicipio"))
                <span class="help-block">{{ $errors->first("fmunicipio") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('festado')) has-error @endif">
                <label for="festado">*Estado</label>
                <input type="text" value="{{ $cliente->festado }}" name="festado" id="festado-field" class="form-control input-sm">
                @if($errors->has("festado"))
                <span class="help-block">{{ $errors->first("festado") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fpais')) has-error @endif">
                <label for="fpais">*Pais</label>
                <input type="text" value="{{ $cliente->fpais }}" name="fpais" id="fpais-field" class="form-control input-sm">
                @if($errors->has("fpais"))
                <span class="help-block">{{ $errors->first("fpais") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fcp')) has-error @endif">
                <label for="fcp">*C.P.</label>
                <input type="text" value="{{ $cliente->fcp }}" name="fcp" id="fcp-field" class="form-control input-sm">
                @if($errors->has("fcp"))
                <span class="help-block">{{ $errors->first("fcp") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('regimen_fiscal_id')) has-error @endif">
                <label for="regimen_fiscal_id">*Regimen Fiscal</label>
                <select class="form-control" id="regimen_fiscal_id-field" name="regimen_fiscal_id" >
                        <option value="" style="display: none;" {{ old('regimen_fiscal_id', optional($cliente)->regimen_fiscal_id ?: '') == '' ? 'selected' : '' }} disabled selected> Seleccionar opción </option>
                    @foreach ($regimenFiscal as $key => $regimenFiscalLabel)
                        <option value="{{ $key }}" {{ old('regimen_fiscal_id', optional($cliente)->regimen_fiscal_id) == $key ? 'selected' : '' }}>
                            {{ $regimenFiscalLabel }}
                        </option>
                    @endforeach
                </select>
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

    </form>
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
