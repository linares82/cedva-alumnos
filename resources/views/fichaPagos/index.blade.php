@extends('layouts.master1')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="page-header">
            <h1>
                Ficha Pagos / Adeudos
                <small>
                    <i class="ace-icon fa fa-angle-double-right"></i>

                </small>
            </h1>
        </div>
    </div>
    <div class="col-md-8">
        <div class="profile-user-info profile-user-info-striped">
            <div class="profile-info-row">
                <div class="profile-info-name"> Escuela </div>
                <div class="profile-info-value">
                     {{ $cliente->plantel->nombre_corto }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> Alumno </div>
                <div class="profile-info-value">
                     {{ $cliente->nombre }} {{ $cliente->nombre2 }} {{ $cliente->ape_paterno }} {{ $cliente->ape_materno }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> Matricula </div>
                <div class="profile-info-value">
                     {{ Auth::user()->name }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> Email </div>
                <div class="profile-info-value">
                     {{ $cliente->mail }}
                </div>
            </div>
        </div>

    </div>
    <div class="col-md-12 alert alert-block alert-success">
        Les recordamos que en caso de requerir factura esto lo puede hacer durante las 72 hrs siguientes después de realizar su pago. Cualquier duda estamos a sus ordenes en el correo facturacion@grupocedva.com
    </div>

    <div class="col-md-12">
        @php
            $j=0;
        @endphp
        @foreach($combinaciones as $combinacion)
        <div class="widget-box widget-color-orange ui-sortable-handle" id="widget-box-1">
            <div class="widget-header">
                <h5 class="widget-title">Detalle de Pagos de {{ $combinacion->grado->name}}</h5>
                <div class="widget-toolbar">
                    <div class="widget-menu">
                        <a href="#" data-action="settings" data-toggle="dropdown">
                            <i class="ace-icon fa fa-bars"></i>
                        </a>
                    </div>
                    <a href="#" data-action="fullscreen" class="orange2">
                        <i class="ace-icon fa fa-expand"></i>
                    </a>
                    <a href="#" data-action="collapse">
                        <i class="ace-icon fa fa-chevron-up"></i>
                    </a>
                </div>
            </div>

            <div class="widget-body">
                <div class="widget-main no-padding">
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <th>No.</th><th>Concepto</th><th>Monto</th><th>Fecha Limite Pago</th><th>Pagado</th><th>Ticket</th>
                        </thead>
                        <tbody>
                            <?php
                            //dd($combinacion->toArray());

                            $adeudos=\App\Adeudo::where('combinacion_cliente_id',$combinacion->id)
                            ->whereNull('deleted_at')
                            ->with('cajaConcepto')
                            ->with('pagoOnLine')
                            //->orderBy('pagado_bnd', 'desc')
                            ->orderBy('fecha_pago')
                            ->get();

                            //dd($adeudos->toArray());
                            $i=0;
                            $bandera_pagar_en_linea=0;
                            ?>

                            @foreach($adeudos as $adeudo)

                                @if($adeudo->cajaConcepto->bnd_mensualidad==1 and isset($adeudo->pagoOnLine))
                                @php
                                    $j++;
                                @endphp
                                <tr>
                                    <td>{{ ++$i }}</td>
                                    <td class="">{{ $adeudo->cajaConcepto->name }}</td>
                                    <td>
                                        @if($adeudo->pagado_bnd==1)
                                        @php

                                        $pagos = App\Pago::where('caja_id', $adeudo->caja_id)->whereNull('deleted_at')->get();
                                        //dd($pagos->toArray());
                                        $total_pagos = 0;
                                        foreach ($pagos as $pago) {
                                            $total_pagos = $total_pagos + $pago->monto;
                                        }
                                        @endphp
                                        {{ number_format($total_pagos,2) }}
                                        @else
                                        {{ optional($adeudo->pagoOnLine)->total }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($adeudo->pagado_bnd==1)
                                        @elseif(isset(optional($adeudo->pagoOnLine)->fecha_limite))
                                        {{ date_format(date_create(optional($adeudo->pagoOnLine)->fecha_limite),'d-m-Y') }}
                                        @endif
                                    </td>
                                    <td>

                                        @php
                                        $peticion=$adeudo->pagoOnLine->peticionMultipago;
                                        $respuesta_msj="";
                                        if(!is_null($peticion)){
                                            $respuesta=\App\SuccessMultipago::where('mp_order',$peticion->mp_order)
                                            ->where('mp_reference',$peticion->mp_reference)
                                            ->where('mp_amount',$peticion->mp_amount)
                                            ->first();
                                            if(!is_null($respuesta)){
                                                $respuesta_msj=$respuesta->mp_responsemsg;
                                            }
                                        }

                                        $existe_seccion_valida=0;
                                        //dd($adeudo->combinacionCliente->grado->seccion);
                                        $resultado=$secciones_validas->where('name', $adeudo->combinacionCliente->grado->seccion)->first();
                                        if(!is_null($resultado)){
                                            $existe_seccion_valida=1;
                                        }

                                        //dd($existe_seccion_valida);

                                        //Revisar si tiene adeudos que no sean mensualidad para no dejarlo pagar
                                        $adeudos_no_mensualidad=\App\Adeudo::where('combinacion_cliente_id',$combinacion->id)
                                        ->join('caja_conceptos as cc','cc.id','adeudos.caja_concepto_id')
                                        ->whereNull('adeudos.deleted_at')
                                        ->where('bnd_mensualidad','<>',1)
                                        ->where('adeudos.pagado_bnd','<>',1)
                                        ->where('adeudos.fecha_pago','<=',$adeudo->fecha_pago)
                                        //->orderBy('pagado_bnd', 'desc')
                                        ->count();

                                        @endphp


                                        @if($adeudos_no_mensualidad>0)
                                            Adeudo pendiente comunicarse al plantel
                                        @endif
                                        @if($adeudo->pagado_bnd==1)
                                            <span class="badge badge-success"><i class="ace-icon fa fa-check"></i>SI</span>
                                        @elseif($adeudo->pagado_bnd==0 and
                                                isset(optional($adeudo->pagoOnLine)->total) and
                                                $adeudo->fecha_pago>date('Y-m-d') and
                                                $existe_seccion_valida==1 and
                                                $adeudos_no_mensualidad==0)
                                            <span class="badge badge-warning"><i class="glyphicon glyphicon-remove"></i></i>NO-{{ $respuesta_msj }} </span>
                                            @if($bandera_pagar_en_linea==0)
                                                <!--@@if(optional($peticion)->mp_paymentmethod=="SUC")
                                                <a href="{{ route('fichaAdeudos.verDetalle', array('adeudo_pago_online_id'=>optional($adeudo->pagoOnLine)->id)) }}" class="btn btn-pink btn-xs">Pagar en linea<i class="ace-icon fa fa-credit-card"></i></a>
                                                @@else @@if(!isset($peticion)) -->
                                                <a href="{{ route('fichaAdeudos.verDetalle', array('adeudo_pago_online_id'=>optional($adeudo->pagoOnLine)->id)) }}" class="btn btn-pink btn-xs">Pagar en linea<i class="ace-icon fa fa-credit-card"></i></a>
                                                <!--@@endif-->
                                            @endif
                                            @php
                                                $bandera_pagar_en_linea=1;
                                            @endphp
                                        <!--<button type="button" class="btn btn-pink btn-xs btnCrearCajaPagoPeticion" data-adeudo_pago_on_line="{{ optional($adeudo->pagoOnLine)->id}}">Pagar en linea<i class="ace-icon fa fa-credit-card"></i></button>-->
                                        @elseif($adeudo->pagado_bnd==0 and
                                                isset(optional($adeudo->pagoOnLine)->total) and
                                                $existe_seccion_valida==1 and
                                                $adeudos_no_mensualidad==0)
                                            <span class="badge badge-danger"><i class="glyphicon glyphicon-remove"></i>NO-{{ $respuesta_msj }} </span>

                                            @if($bandera_pagar_en_linea==0)
                                            <!--@@if(optional($peticion)->mp_paymentmethod=="SUC")
                                            <a href="{{ route('fichaAdeudos.verDetalle', array('adeudo_pago_online_id'=>optional($adeudo->pagoOnLine)->id)) }}" class="btn btn-pink btn-xs">Pagar en linea<i class="ace-icon fa fa-credit-card"></i></a>
                                            @@else@if(!isset($peticion))-->
                                            <a href="{{ route('fichaAdeudos.verDetalle', array('adeudo_pago_online_id'=>optional($adeudo->pagoOnLine)->id)) }}" class="btn btn-pink btn-xs">Pagar en linea<i class="ace-icon fa fa-credit-card"></i></a>
                                            <!--@@endif-->
                                            @endif
                                            @php
                                                $bandera_pagar_en_linea=1;
                                            @endphp
                                        <!--<button type="button" class="btn btn-pink btn-xs btnCrearCajaPagoPeticion" data-adeudo_pago_on_line="{{ optional($adeudo->pagoOnLine)->id}}">Pagar en linea<i class="ace-icon fa fa-credit-card"></i></button>-->
                                        @endif
                                        <div id='loading1' style='display: none'><img src="{{ asset('img/ajax-loader.gif') }}" title="Enviando" /></div>

                                    </td>
                                    <td>

                                        @if($adeudo->pagado_bnd==1 and $adeudo->caja_id>0)
                                            {{ $adeudo->caja->consecutivo }}
                                            <a href="{{ route('fichaAdeudos.imprimir', array('pago'=>$adeudo->caja->pago->id)) }}" target="_blank" class="btn btn-info btn-xs">
                                                Imprimir
                                                <i class="ace-icon fa fa-print  align-top bigger-125 icon-on-right"></i>
                                            </a>

                                            <!--@@if(Auth::user()->nivel==0 )-->

                                            @php
                                            $mesHoy=Carbon\Carbon::createFromFormat('Y-m-d', date('Y-m-d'))->month;
                                            $anioHoy=Carbon\Carbon::createFromFormat('Y-m-d', date('Y-m-d'))->year;
                                            $mesFechaPago=Carbon\Carbon::createFromFormat('Y-m-d', $adeudo->caja->pago->fecha)->month;
                                            $anioFechaPago=Carbon\Carbon::createFromFormat('Y-m-d', $adeudo->caja->pago->fecha)->year;
                                            $fechaPago=Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $adeudo->caja->pago->updated_at);
                                            $fechaHoy=Carbon\Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));
                                            //dd($fechaPago->diffInHours($fechaHoy));

                                            @endphp

                                            @if(is_null($adeudo->caja->pago->uuid) and
                                                is_null($adeudo->caja->pago->cbb) and
                                                is_null($adeudo->caja->pago->xml) and
                                                $mesHoy==$mesFechaPago and
                                                $anioHoy==$anioFechaPago
						 /*and
                                                $fechaPago->diffInHours($fechaHoy)<=72*/)
                                                <a href="{{ route('fichaAdeudos.datosFactura', array('pagoOnLine'=>$adeudo->pagoOnLine->id)) }}" class="btn btn-inverse btn-xs">
                                                    Facturar
                                                    <i class="ace-icon fa fa-money align-top bigger-125 icon-on-right"></i>
                                                </a>
                                            @elseif(!is_null($adeudo->caja->pago->uuid) and
                                                    !is_null($adeudo->caja->pago->cbb) and
                                                    !is_null($adeudo->caja->pago->xml))
                                                <a href="{{ route('fichaAdeudos.getFacturaPdfByUuid', array('uuid'=>$adeudo->pagoOnLine->pago->uuid)) }}" target="_blank" class="btn btn-white btn-success btn-xs">
                                                    <i class="ace-icon fa fa-download"></i> Pdf
                                                </a>

                                                <a href="{{ route('fichaAdeudos.getFacturaXmlByUuid', array('uuid'=>$adeudo->pagoOnLine->pago->uuid)) }}" class="btn btn-info btn-white btn-xs">
                                                    <i class="ace-icon fa fa-download"></i> Xml
                                                </a>
                                            @endif
                                            <!--@@endif-->
                                        @endif

                                    </td>
                                </tr>
                                @endif
                            @endforeach

                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @if($j==0)
            Sin informacion encontrada, por favor acudir a su plantel de inscripcion.
        @endif
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
$(document).ready(function(){
    $('.btnCrearCajaPagoPeticion').click(function(){
        //console.log($(this).data('adeudo_pago_on_line'));
        $.ajax({
                type: 'POST',
                url: '{{url("/fichaAdeudos/crearCajaPagoPeticion")}}',
                data: {
                    '_token': $('input[name=_token]').val(),
                    'adeudo_pago_online_id': $(this).data('adeudo_pago_on_line')
                },
                beforeSend : function(){
                    $("#loading1").show();
                },
                complete : function(){
                    $("#loading1").hide();
                },
                success: function(data) {

                    if(referencia_check==0){
                        $('#form-buscarVenta').submit();
                    }else{
                        console.log(data.datos)
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

                        $('#frm_multipagos').attr("action", data.datos.url_peticion);
                        $('#frm_multipagos').submit();
                    }

                }
            });

});

    });

</script>
@endpush

