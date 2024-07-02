@extends('layouts.master1')

@section('content')
<div class="row">
    <div class="col-md-12"><h1>Datos para el pago</h1></div>
    <div class="col-md-6">
        <div class="profile-user-info profile-user-info-striped">
            <div class="profile-info-row">
                <div class="profile-info-name"> Matricula </div>
                <div class="profile-info-value">
                     {{ $adeudo_pago_online->cliente->matricula }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> Concepto </div>
                <div class="profile-info-value">
                     {{ $adeudo_pago_online->adeudo->cajaConcepto->name }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> Monto a Cobrar </div>
                <div class="profile-info-value">
                     {{ $adeudo_pago_online->total }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> Referencia Unica(Solo Pago en Banco) </div>
                <div class="profile-info-value">

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
                        <div class="col-sm-12">
                        <label for="forma_pago_id" class="control-label">Forma Pago</label>

                        <select class="form-control chosen" id="forma_pago_id" name="forma_pago_id" required="true">
                            <option value=""   selected>Seleccionar opción</option>
                            @foreach ($forma_pagos as $key => $forma_pago)
                                <option value="{{ $key }}">
                                    {{ $forma_pago }}
                                </option>
                            @endforeach
                        </select>

                        </div>

                        <div class="col-sm-12">
                            <label for="name">Nombre(s)</label>
                            <input type="text"  value="{{ $adeudo_pago_online->cliente->nombre }} {{ $adeudo_pago_online->cliente->nombre2 }}" id="name" placeholder="Nombre(s)" class="col-xs-12 col-sm-12">
                        </div>
                        <div class="col-sm-12">
                        <label for="last_name">Apellidos</label>
                        <input type="text" value="{{ $adeudo_pago_online->cliente->ape_paterno }} {{ $adeudo_pago_online->cliente->ape_materno }}" id="last_name" placeholder="Apellidos" class="col-xs-12 col-sm-12">
                        </div>
                        <div class="col-sm-12">
                        <label for="phone_number">Teléfono</label>
                        <input type="text" value="{{ $adeudo_pago_online->cliente->tel_fijo }}" id="phone_number"  placeholder="Teléfono" class="col-xs-12 col-sm-12">
                        </div>
                        <div class="col-sm-12">
                        <label for="email">Email</label>
                        <input type="text" value="{{ $adeudo_pago_online->cliente->mail }}" id="email" placeholder="email" class="col-xs-12 col-sm-12">
                        </div>






                        <div class="clearfix form-actions align-center">
                            <div id="content"></div>
                            @php
                                $hoy=\Carbon\Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
                                //dd($adeudo_pago_online->fecha_limite);
                                //$fecha_limite=\Carbon\Carbon::createFromFormat('Y-m-d', $adeudo_pago_online->fecha_limite);
                                //dd($fecha_limite);
                            @endphp

                            <!--@@if($adeudo_pago_online->fecha_limite->greaterThanOrEqualTo($hoy))-->
                            <button class="btn btn-info" id="bootbox-confirm">
                                <i class="ace-icon fa fa-check bigger-110"></i>
                                Confirmar
                            </button>
                            <!--@@endif-->
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
        console.log(forma_pago);
        let name=$("#name").val();
        let last_name=$("#last_name").val();
        let phone_number=$("#phone_number").val();
        let email=$("#email").val();
        //console.log(forma_pago);
        if(forma_pago==="Seleccionar opción" || name==="" || last_name==='' || phone_number==="" || email===""){
            alert('Todos los campos son necesarios.');

        }else{

        bootbox.confirm({
            message: `Confirmar datos de pago: <br>
                    Forma de pago: ${forma_pago} <br>
                    Nombre: ${name} <br>
                    Apellidos: ${last_name} <br>
                    Teléfono: ${phone_number} <br>
                    Email: ${email}
                    `,
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
                        url: '{{ route("fichaAdeudos.crearCajaPagoPeticionOpenpay") }}',
                        type: 'POST',
                        data: {
                            '_token': $('input[name=_token]').val(),
                            "adeudo_pago_online_id":{{ $adeudo_pago_online->id }},
                            "forma_pago_id":$("#forma_pago_id option:selected").val(),
                            'name':$("#name").val(),
                            'last_name':$("#last_name").val(),
                            'phone_number':$("#phone_number").val(),
                            'email':$("#email").val(),
                        },
                        dataType: 'json',
                        beforeSend : function(){$("#loading13").show();},
                        complete : function(){$("#loading13").hide();},
                        success: function(data){

                            if(data.method==="card"){
                                window.location.replace(data.url);
                            }else if(data.method==="bank_account"){
                                window.open(data.url);
                            }

                        }
                    });
                }
            }
            }
        );
        }
    });
});

function formatoFecha(texto){
  return texto.replace(/^(\d{4})-(\d{2})-(\d{2})$/g,'$1/$2/$3');
}

</script>
@endpush
