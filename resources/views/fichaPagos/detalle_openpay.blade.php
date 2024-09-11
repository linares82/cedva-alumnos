@extends('layouts.master1')

@section('content')
<style>
.card-expl {
    float: left;
    height: 80px;
    margin: 20px 0;
    width: 800px;
}
.card-expl div {
    background-position: left 45px;
    background-repeat: no-repeat;
    height: 70px;
    padding-top: 10px;
}
.card-expl div.debit {
    background-image: url("{{asset('img/openpay/cards2.png')}}");
    margin-left: 20px;
    width: 540px;
}
.card-expl div.credit {
    background-image: url("{{asset('img/openpay/cards1.png')}}");
    margin-left: 30px;
    width: 209px;
}

</style>
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

    @if(!is_null(($peticionesOpenpay)) and count($peticionesOpenpay)>0)
    <div class="col-sm-8 col-sm-offset-2">
        <div class="widget-box">
            <div class="widget-header">
                <h5 class="widget-title">Peticiones de pago existentes</h5>
            </div>
            <div class="widget-body">
                <div class="widget-main">
                @foreach($peticionesOpenpay as $peticion)
                    @if($peticion->pmethod=="card")
                        Se detecto una peticion de pago con tarjeta (debito, credito o servicios) con estatus 'No completada' con order {{$peticion->porder_id}}
                        <button class="btn btn-minier btn-purple enviarForm" data-forma_pago_id="{{$peticion->forma_pago_id}}",
                                                                data-name='{{$peticion->pname}}'
                                                                data-last_name='{{$peticion->plast_name}}'
                                                                data-phone_number='{{$peticion->pphone_number}}'
                                                                data-email='{{$peticion->pemail}}'>Completar Operacion</button>
                        <hr/>
                    @elseif($peticion->pmethod=="bank_account")
                        Se detecto una peticion de pago en banco (Deposito o transferencia) con estatus 'No completada' con order {{$peticion->porder_id}}
                        <button class="btn btn-minier btn-yellow enviarForm" data-forma_pago_id="{{$peticion->forma_pago_id}}",
                                                                data-name='{{$peticion->pname}}'
                                                                data-last_name='{{$peticion->plast_name}}'
                                                                data-phone_number='{{$peticion->pphone_number}}'
                                                                data-email='{{$peticion->pemail}}'>Completar Operacion</button>
                        <hr/>
                    @elseif($peticion->pmethod=="store")
                        Se detecto una peticion de pago en tiendas(paynet) con estatus 'No completada' con order {{$peticion->porder_id}}
                        <button class="btn btn-minier btn-pink enviarForm" data-forma_pago_id="{{$peticion->forma_pago_id}}",
                                                                data-name='{{$peticion->pname}}'
                                                                data-last_name='{{$peticion->plast_name}}'
                                                                data-phone_number='{{$peticion->pphone_number}}'
                                                                data-email='{{$peticion->pemail}}'>Completar Operacion</button>
                        <hr/>
                    @endif
                @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-sm-8 col-sm-offset-2">
        <div class="widget-box">
            <div class="widget-header">
                <h5 class="widget-title">Llenar opciones</h5>
            </div>

            <div class="widget-body">
                <div class="widget-main">

                    <form action="#" method="POST" id="payment-form">
                        <div class="col-sm-12">
                        <label for="forma_pago_id" class="control-label">Forma Pago</label>

                        <select class="form-control chosen" id="forma_pago_id" name="forma_pago_id" required="true">
                            <option value="" selected>Seleccionar opción</option>
                            @foreach ($forma_pagos as $key => $forma_pago)
                                <option value="{{ $key }}">
                                    {{ $forma_pago }}
                                </option>
                            @endforeach
                        </select>

                        </div>
                        <div class="col-sm-12 col-sm-offset-0" id="datos-tarjeta" style='display: none'>

                                <label for="name">Datos-Tarjeta</label><br>

                                <div class="col-md-6">
                                    <label>Titular</label>
                                    <input type="text" style="width:100%;" autocomplete="off" id="holder_name" data-openpay-card="holder_name" placeholder="" maxlength="16" >
                                </div>
                                <div class="col-md-6">
                                    <label>Número de tarjeta</label>
                                    <input type="text" style="width:100%;" autocomplete="off" id="card_number" data-openpay-card="card_number" placeholder="0000 0000 0000 0000" maxlength="16" >
                                </div>
                                <div class="col-sm-12">
                                    <label style="width:100%;">Fecha de expiración</label>
                                    <input type="text" placeholder="Mes, 2 digitos" data-openpay-card="expiration_month" id="expiration_month" maxlength="2">
                                    <input type="text" placeholder="Año, 2 digitos" data-openpay-card="expiration_year" id="expiration_year" maxlength="2">
                                </div>
                                <div class="col-sm-12">
                                    <div class="col-sm-6">
                                        <label>Codigo de Seguridad</label>
                                        <input type="text" style="width:100%;" placeholder="" autocomplete="off" data-openpay-card="cvv2" id="cvv2" maxlength="4" >
                                    </div>
                                    <div class="col-sm-6">
                                        <img src="{{asset('img/openpay/cvv.png')}}" alt="cvv">
                                    </div>

                                </div>
                                <div class="col-sm-3" >
                                <div class="card-expl">
                                    <div class="credit"><h6>Tarjetas de crédito</h6></div>
                                </div>
                                </div>
                                <div class="col-sm-9" >
                                <div class="card-expl">
                                    <div class="debit"><h6>Tarjetas de débito</h6></div>
                                </div>
                                </div>


                        </div>


                        <div class="col-sm-12">
                            <input type="hidden" name="token_3d_secure" id="token_3d_secure">
                            <div id="loading_seguridad" style="display:none;"><span class="text-success">Protegiendo informacion ...</span></div>
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
                        <div class="col-lg-6">
                            Transacciones realizadas vía: <br>
                            <img src="{{asset('img/openpay/openpay.png')}}" alt="Logo Openpay">
                        </div>
                        <div class="col-sm-6">
                        <img src="{{asset('img/openpay/security.png')}}" alt="Escudo">
                        Tus pagos se realizan de forma segura con encriptación de 256 bits

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


@endsection

@push('scripts')
<script type="text/javascript"
        src="https://js.openpay.mx/openpay.v1.min.js"></script>
<script type='text/javascript'
  src="https://js.openpay.mx/openpay-data.v1.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('#forma_pago_id').change(function(){
        let posicion = $('#forma_pago_id option:selected').text().toLowerCase().indexOf('tarjeta');
        if(posicion>0){
            $("#datos-tarjeta").show();
        }else{
            $("#datos-tarjeta").hide();
        }

    });

    $(document).on('click', '.enviarForm', function(e) {
        $('#content').html('<div class="loading"><img src="{{ asset('img/ajax-loader.gif') }}" alt="loading" /><br/>Un momento, por favor...</div>');

        $.ajax({
            url: '{{ route("fichaAdeudos.crearCajaPagoPeticionOpenpay") }}',
            type: 'POST',
            data: {
                '_token': $('input[name=_token]').val(),
                "adeudo_pago_online_id":"{{ $adeudo_pago_online->id }}",
                "forma_pago_id": $(this).data('forma_pago_id'),
                'name': $(this).data('name'),
                'last_name': $(this).data('last_name'),
                'phone_number': $(this).data('phone_number'),
                'email': $(this).data('email'),
            },
            dataType: 'json',
            beforeSend : function(){$("#loading13").show();},
            complete : function(){$("#loading13").hide();},
            success: function(data){
                if(data.method==="card" && data.error===null){
                    window.location.replace(data.url);
				}else if(data.method==="card-expirado"){
                    alert('operacion expirada o fallida, repetir peticion de pago');
                    location.reload();
                }else if(data.method==="bank_account" && data.error===null){
                    window.open(data.url);
                }else if(data.method==="store" && data.error===null){
                    window.open(data.url);
                }else if(data.error!==null){
                    if(data.error.error_code===3004 || data.error.error_code===3005 ){
                        alert("6009: En este momento no es posible procesar su peticion");
                    }else{
                        alert(data.error.description);
                    }

                }else{
                    window.location.replace(data.url);
                }
            }
        });
    });

    $("#bootbox-confirm").on(ace.click_event, function(e) {
        e.preventDefault();
        let forma_pago=$("#forma_pago_id option:selected").text();

        let posicion = $('#forma_pago_id option:selected').text().toLowerCase().indexOf('tarjeta');
        holder_name=$("#holder_name").val();
        card_number=$("#card_number").val();
        expiration_month=$("#expiration_month").val();
        expiration_year=$("#expiration_year").val();
        cvv2=$("#cvv2").val();

        //console.log(forma_pago);
        let name=$("#name").val();
        let last_name=$("#last_name").val();
        let phone_number=$("#phone_number").val();
        let email=$("#email").val();
        //console.log(forma_pago);
        if(forma_pago==="Seleccionar opción" || name==="" || last_name==='' || phone_number==="" || email===""){
            alert('Todos los campos son necesarios.');
        }else if(posicion>0){
            if(card_number=="" || expiration_month=="" || expiration_year=="" ||cvv2=="" || holder_name==""){
                alert('Para un pago con Tarjeta debe capturar todos sus campos respectivos.');
            }else{

                OpenPay.setId('{{$plantel->oid}}');
                OpenPay.setApiKey('{{$plantel->opublica}}');
                OpenPay.setSandboxMode({{$openpay_productivo ? false : true}});

                var deviceSessionId = OpenPay.deviceData.setup("payment-form", "deviceIdHiddenFieldName");

                OpenPay.token.extractFormAndCreate('payment-form',
                function(response) {

                    var token_id = response.data.id;
                    $('#token_3d_secure').val(token_id);
                    enviarDatos(forma_pago,name,last_name,phone_number,email,holder_name,card_number,cvv2,expiration_month,expiration_year,token_id,deviceSessionId);
                },
                error_callbak);
                console.log('formulario completo')
                console.log($('payment-form').serialize());


                /*$.ajax({
                    url: "{{route('fichaAdeudos.tokenOpenpay')}}",
                    type: 'GET',
                    data: {
                        'holder_name': holder_name,
                        "card_number": card_number,
                        "cvv2": cvv2,
                        'expiration_month': expiration_month,
                        'expiration_year': expiration_year,
                        'plantel':"{{$plantel->id}}"
                    },
                    beforeSend : function(){
                        $("#loading_seguridad").show();
                    },
                    complete : function(){$("#loading_seguridad").hide();},
                    success: function(data){
                        datos=JSON.parse(data);
                        //console.log();
                        $("#token_3d_secure").val(datos.id);
                        //console.log($("#token_3d_secure").val());
                        //enviarDatos(forma_pago,name,last_name,phone_number,email,holder_name,card_number,cvv2,expiration_month,expiration_year,datos.id);
                    }
                });
                */
            }
        }else{
            enviarDatos(forma_pago,name,last_name,phone_number,email,holder_name,card_number,cvv2,expiration_month,expiration_year,null);
        }
    });
});

var success_callbak = function(response) {
                console.log(response);
              /*var token_id = response.data.id;
              $('#token_id').val(token_id);
              $('#payment-form').submit();*/
};

var error_callbak = function(response) {
     var desc = response.data.description != undefined ?
        response.data.description : response.message;
     alert("ERROR [" + response.status + "] " + desc);
     //$("#pay-button").prop("disabled", false);
};

function enviarDatos(forma_pago,name,last_name,phone_number,email,holder_name,card_number,cvv2,expiration_month,expiration_year,token_3d_secure,device){

    bootbox.confirm({
            message: `Confirmar datos de pago: <br>
                    Forma de pago: ${forma_pago} <br>
                    Nombre: ${name} <br>
                    Apellidos: ${last_name} <br>
                    Teléfono: ${phone_number} <br>
                    Email: ${email} <br>
                    Titular: ${holder_name} <br>
                    No. tarjeta: ${card_number}<br>
                    CVV: ${cvv2} <br>
                    Mes Vencimiento: ${expiration_month} <br>
                    Año Vencimiento: ${expiration_year} <br>
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
                            'holder_name': holder_name,
                            "card_number": card_number,
                            "cvv2": cvv2,
                            'expiration_month': expiration_month,
                            'expiration_year': expiration_year,
                            'token_3d_secure':token_3d_secure,
                            'device':device
                        },
                        dataType: 'json',
                        beforeSend : function(){$("#loading13").show();},
                        complete : function(){$("#loading13").hide();},
                        success: function(data){
                            if(data.method==="card" && data.error===null){
                                window.location.replace(data.url);
                            }else if(data.method==="card-expirado"){
                                alert('operacion expirada, repetir peticion de pago');
                                location.reload();
                            }else if(data.method==="bank_account" && data.error===null){
                                window.open(data.url);
                            }else if(data.method==="store" && data.error===null){
                                window.open(data.url);
                            }else if(data.error!==null){
                                if(data.error.error_code===3004 || data.error.error_code===3005 ){
                                    alert("6009: En este momento no es posible procesar su peticion");
                                }else{
                                    alert(data.error.description);
                                }
                            }else{
                                window.location.replace(data.url);
                            }
                        }
                    });
                }
            }
            }
        );
}

function formatoFecha(texto){
  return texto.replace(/^(\d{4})-(\d{2})-(\d{2})$/g,'$1/$2/$3');
}

</script>
@endpush
