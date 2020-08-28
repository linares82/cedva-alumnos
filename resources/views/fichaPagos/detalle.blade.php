@extends('layouts.master1')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="profile-user-info profile-user-info-striped">
            <div class="profile-info-row">
                <div class="profile-info-name"> Matricula </div>
                <div class="profile-info-value">
                     {{ $adeudo_pago_online->cliente->matricula }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> Nombre </div>
                <div class="profile-info-value">
                     {{ $adeudo_pago_online->cliente->nombre }} {{ $adeudo_pago_online->cliente->nombre2 }} {{ $adeudo_pago_online->cliente->ape_paterno }} {{ $adeudo_pago_online->cliente->ape_materno }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> Concepto </div>
                <div class="profile-info-value">
                     {{ $adeudo_pago_online->adeudo->cajaConcepto->name }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> Monto Normal </div>
                <div class="profile-info-value">
                     {{ $adeudo_pago_online->subtotal }}
                </div>
            </div>
        </div>

    </div>
    <div class="col-md-6">
        <div class="profile-user-info profile-user-info-striped">
            <div class="profile-info-row">
                <div class="profile-info-name"> F. Actual Cobro (dd-mm-yyyy) </div>
                <div class="profile-info-value">
                     {{ $adeudo_pago_online->created_at->format('d-m-Y') }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> F. Vencimiento (dd-mm-yyyy) </div>
                <div class="profile-info-value">
                     {{ $adeudo_pago_online->fecha_limite->format('d-m-Y') }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> . </div>
                <div class="profile-info-value">
                    .
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> Monto a Cobrar </div>
                <div class="profile-info-value">
                     {{ $adeudo_pago_online->total }}
                </div>
            </div>

        </div>
    </div>
    <div class="row">

    <div class="col-sm-6 col-sm-offset-3">
        <div class="widget-box">
            <div class="widget-header">
                <h5 class="widget-title">Llenar opciones</h5>
            </div>

            <div class="widget-body">
                <div class="widget-main">
                    <form action="#" method="POST">

                        <label for="forma_pago_id" class="control-label">Forma Pago</label>

                        <select class="form-control chosen" id="forma_pago_id" name="forma_pago_id" required="true">
                            <option value="" style="display: none;"  disabled selected>Seleccionar opción</option>
                            @foreach ($forma_pagos as $key => $forma_pago)
                                <option value="{{ $key }}">
                                    {{ $forma_pago }}
                                </option>
                            @endforeach
                        </select>

                        <label class="control-label">Persona que realiza el pago:</label>
                        <div class="radio">
                            <label>
                                <input name="persona_pago" value="1" type="radio" class="ace">
                                <span class="lbl"> Alumno </span>
                            </label>
                            <label>
                                <input name="persona_pago" type="radio" value="2" class="ace">
                                <span class="lbl"> Otra Persona  </span>
                            </label>
                        </div>
                        <input type="text" id="otra_persona" placeholder="Nombre de Otra Persona" class="col-xs-12 col-sm-12">
                        <br><br>
                        <div class="clearfix form-actions align-center">
                            <div id="content"></div>
                            @php
                                $hoy=\Carbon\Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
                                //dd($adeudo_pago_online->fecha_limite);
                                //$fecha_limite=\Carbon\Carbon::createFromFormat('Y-m-d', $adeudo_pago_online->fecha_limite);
                                //dd($fecha_limite);
                            @endphp

                            @if($adeudo_pago_online->fecha_limite->greaterThan($hoy))
                            <button class="btn btn-info" id="bootbox-confirm">
                                <i class="ace-icon fa fa-check bigger-110"></i>
                                Confirmar
                            </button>
                            @endif
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<div id="formulario_multipagos">
    <form method="post" id="frm_multipagos" action="about:blank">
        <input type="hidden" name="mp_account" id="mp_account" value="">
        <input type="hidden" name="mp_product" id="mp_product" value="">
        <input type="hidden" name="mp_order" id="mp_order" value="">
        <input type="hidden" name="mp_reference" id="mp_reference" value="">
        <input type="hidden" name="mp_node" id="mp_node" value="">
        <input type="hidden" name="mp_concept" id="mp_concept" value="">
        <input type="hidden" name="mp_amount" id="mp_amount" value="">
        <input type="hidden" name="mp_customername" id="mp_customername" value="">
        <input type="hidden" name="mp_currency" id="mp_currency" value="">
        <input type="hidden" name="mp_signature" id="mp_signature" value="">
        <input type="hidden" name="mp_urlsuccess" id="mp_urlsuccess" value="">
        <input type="hidden" name="mp_urlfailure" id="mp_urlfailure" value="">
        <input type="hidden" name="mp_paymentmethod" id="mp_paymentmethod" value="">
        <input type="hidden" name="mp_datereference" id="mp_datereference" value="">
    </form>
</div>

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function(){

    $("#bootbox-confirm").on(ace.click_event, function(e) {
        e.preventDefault();
        let forma_pago=$("#forma_pago_id option:selected").text();
        //console.log(forma_pago);
        let pagador;
        if($('input:radio[name=persona_pago]:checked').val()=="1"){
            pagador="{{ $adeudo_pago_online->cliente->nombre }} {{ $adeudo_pago_online->cliente->nombre2 }} {{ $adeudo_pago_online->cliente->ape_paterno }} {{ $adeudo_pago_online->cliente->ape_materno }}";
        }else{
            pagador=$('#otra_persona').val();
        }
        //console.log(pagador);
        bootbox.confirm({
            message: "Confirmar datos de pago: <br>"+
                    "Forma de pago:"+forma_pago+"<br>"+
                    "Persona que paga:"+pagador,
            buttons: {
                confirm: {
                    label: "Pagar",
                    className: "btn-primary btn-sm",
                },
                cancel: {
                    label: "Cancelar",
                    className: "btn-sm",
                }
            },
            callback: function(result) {
                if(result){
                    $('#content').html('<div class="loading"><img src="{{ asset('img/ajax-loader.gif') }}" alt="loading" /><br/>Un momento, por favor...</div>');
                    $.ajax({
                        url: '{{ route("fichaAdeudos.crearCajaPagoPeticion") }}',
                        type: 'POST',
                        data: {
                            '_token': $('input[name=_token]').val(),
                            "adeudo_pago_online_id":{{ $adeudo_pago_online->id }},
                            "forma_pago_id":$("#forma_pago_id option:selected").val(),
                            "pagador":pagador
                        },
                        dataType: 'json',
                        beforeSend : function(){$("#loading13").show();},
                        complete : function(){$("#loading13").hide();},
                        success: function(data){
                            $("#mp_account").val(data.datos.mp_account);
                            $("#mp_product").val(data.datos.mp_product);
                            $("#mp_order").val(data.datos.mp_order);
                            $("#mp_reference").val(data.datos.mp_reference);
                            $("#mp_node").val(data.datos.mp_node);
                            $("#mp_concept").val(data.datos.mp_concept);
                            $("#mp_amount").val(data.datos.mp_amount);
                            $("#mp_customername").val(data.datos.mp_customername);
                            $("#mp_currency").val(data.datos.mp_currency);
                            $("#mp_order").val(data.datos.mp_order);
                            $("#mp_signature").val(data.datos.mp_signature);
                            $("#mp_urlsuccess").val(data.datos.mp_urlsuccess);
                            $("#mp_urlfailure").val(data.datos.mp_urlfailure);
                            $("#mp_paymentmethod").val(data.datos.mp_paymentmethod);
                            $("#mp_datereference").val(formatoFecha(data.datos.mp_datereference));

                            $('#frm_multipagos').attr("action", data.datos.url_peticion);
                            $('#frm_multipagos').submit();
                        }
                    });
                }
            }
            }
        );
    });
});

function formatoFecha(texto){
  return texto.replace(/^(\d{4})-(\d{2})-(\d{2})$/g,'$1/$2/$3');
}

</script>
@endpush
