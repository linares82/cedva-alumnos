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
                     {{ $cliente->plantel->razon }}
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


    <div class="col-md-12">
        @php
            $j=0;
        @endphp
        @foreach($combinaciones as $combinacion)
        <div class="widget-box widget-color-orange ui-sortable-handle" id="widget-box-1">
            <div class="widget-header">
                <h5 class="widget-title">Detalle de Pagos de {{ $combinacion->grado->name }}</h5>
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
                            @php
                            $adeudos=\App\Adeudo::where('combinacion_cliente_id',$combinacion->id)
                            ->with('cajaConcepto')
                            ->with('pagoOnLine')
                            ->get();

                            //dd($adeudos);
                            $i=0;
                            @endphp

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
                                    {{ number_format(optional($adeudo->caja)->total,2) }}
                                    @else
                                    {{ optional($adeudo->pagoOnLine)->total }}
                                    @endif
                                </td>
                                <td>
                                    @if(isset(optional($adeudo->pagoOnLine)->fecha_limite))
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
                                    @endphp

                                    @if($adeudo->pagado_bnd==1)
                                    <span class="badge badge-success"><i class="ace-icon fa fa-check"></i>SI-{{ $respuesta_msj }}</span>
                                    @elseif($adeudo->pagado_bnd==0 and isset(optional($adeudo->pagoOnLine)->total) and $adeudo->fecha_pago>date('Y-m-d'))
                                    <span class="badge badge-warning"><i class="glyphicon glyphicon-remove"></i></i>NO-{{ $respuesta_msj }}</span>
                                    <a href="{{ route('fichaAdeudos.verDetalle', array('adeudo_pago_online_id'=>optional($adeudo->pagoOnLine)->id)) }}" class="btn btn-pink btn-xs">Pagar en linea<i class="ace-icon fa fa-credit-card"></i></a>
                                    <!--<button type="button" class="btn btn-pink btn-xs btnCrearCajaPagoPeticion" data-adeudo_pago_on_line="{{ optional($adeudo->pagoOnLine)->id}}">Pagar en linea<i class="ace-icon fa fa-credit-card"></i></button>-->
                                    @elseif($adeudo->pagado_bnd==0 and isset(optional($adeudo->pagoOnLine)->total) and $adeudo->fecha_pago<date('Y-m-d'))
                                    <span class="badge badge-danger"><i class="glyphicon glyphicon-remove"></i>NO-{{ $respuesta_msj }}</span>
                                    <a href="{{ route('fichaAdeudos.verDetalle', array('adeudo_pago_online_id'=>optional($adeudo->pagoOnLine)->id)) }}" class="btn btn-pink btn-xs">Pagar en linea<i class="ace-icon fa fa-credit-card"></i></a>
                                    <!--<button type="button" class="btn btn-pink btn-xs btnCrearCajaPagoPeticion" data-adeudo_pago_on_line="{{ optional($adeudo->pagoOnLine)->id}}">Pagar en linea<i class="ace-icon fa fa-credit-card"></i></button>-->
                                    @endif
                                    <div id='loading1' style='display: none'><img src="{{ asset('img/ajax-loader.gif') }}" title="Enviando" /></div>


                                </td>
                                <td>

                                    @if($adeudo->pagado_bnd==1 )
                                    {{ $adeudo->caja->consecutivo }}
                                    <a href="{{ route('fichaAdeudos.imprimir', array('pago'=>$adeudo->caja->pago->id)) }}" target="_blank" class="btn btn-info btn-xs">
                                        Imprimir
                                        <i class="ace-icon fa fa-print  align-top bigger-125 icon-on-right"></i>
                                    </a>
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

