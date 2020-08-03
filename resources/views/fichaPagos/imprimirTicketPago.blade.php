<html>
<head>
    <style>

        body{
            font-family:"Arial";
            font-size:small;
        }
        </style>
</head>

<body>
@php
$sucursales=App\Plantel::where('rfc',$cliente->plantel->rfc)->get();
@endphp

<div id="printeArea">
<table style="width:100%;height:auto;border:1px solid #ccc;font-size: 0.70em;">
    <tr>
        <td align="center" colspan="2">
            @if(isset($combinacion->especialidad->imagen))
                <img src="{{asset('storage/especialidads/'.$combinacion->especialidad->imagen)}}"
                    alt='img' style='width: 100px;
                    margin: 4px;'>
            @endif
        </td>
    </tr>
    <tr><td colspan="2" align="center">
        @php
        $cadena='Id:'.$cliente->id.
                '; Nombre:'.$cliente->nombre.' '.$cliente->nombre2.' '.$cliente->ape_paterno.' '.$cliente->ape_materno.
                '; Plantel:'.$cliente->plantel->razon;
        $cadena_pie='cliente:'.$cliente->id.'; Plantel:'.$cliente->plantel->id.";Caja:".$caja->consecutivo;
        foreach($caja->cajaLns as $caja_linea){
            if($caja_linea->cajaConcepto->id==1){
                $cadena=$cadena.';'.$caja_linea->cajaConcepto->name." (".$caja_linea->adeudo->fecha_pago.")";
            }else{
                $cadena=$cadena.';'.$caja_linea->cajaConcepto->name;
            }

        }
        $cadena=$cadena.'; Total:'.number_format($caja->total, 2).'; '.$impresion_token->toke_unico;
        @endphp


        <img src="data:image/png;base64,
                            {!! base64_encode(QrCode::format('png')->size(80)->generate($cadena_pie)) !!} ">

    </td></tr>
    <tr><td colspan="2" align="center" >{{$cliente->plantel->razon}}</td></tr>
    <tr>
        <td colspan="2" align="center" >

        </td>
    </tr>

    <tr>
        <td colspan="2" >

            @if($combinacion)
            Estudios:{{$combinacion->especialidad->name." / ".
                       $combinacion->nivel->name." / ".
                       $combinacion->grado->name}}
            @endif
        </td>
    </tr>

    <tr>
        <td colspan="2" >
            @if($caja->st_caja_id==1)
                Ticket {{$caja->consecutivo}} pagado el {{$caja->fecha}}
            @elseif($caja->st_caja_id==2)
                Ticket {{$caja->consecutivo}} cancelado el {{$caja->fecha}}
            @else
                Ticket {{$caja->consecutivo}} en espera de su pago
            @endif
        </td>
    </tr>

    <tr>
        <td colspan="2">
            Atendido por: {{ $empleado->nombre." ".$empleado->ape_paterno." ".$empleado->ape_materno }}
        </td>
    </tr>
    <tr>
        <td colspan="2">
            Alumno:{{$cliente->id."-".$cliente->nombre." ".$cliente->nombre2." ".$cliente->ape_paterno." ".$cliente->ape_materno}}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td width="50%">
            Serv. Educ.
        </td>
        <td>
            F. Limite Pago
        </td>
        <td align="right"> Monto </td>
    </tr>
    <?php $total=0; ?>
    @foreach($caja->cajaLns as $caja_linea)

    <tr>
        <td>
            @php
            $conceptoMensualidad=explode(' ',$caja_linea->cajaConcepto->name);

            @endphp
            @if($caja_linea->cajaConcepto->id==1)
                {{$caja_linea->cajaConcepto->name." (".$caja_linea->adeudo->fecha_pago.")"}}
            @else
                @if($conceptoMensualidad[0]="Mensualidad")
                    {{ $conceptoMensualidad[1] }}
                @else
                {{$caja_linea->cajaConcepto->name}}
                @endif
            @endif
        </td>
        <td>
            @if (isset($caja_linea->adeudo->fecha_pago))
            {{$caja_linea->adeudo->fecha_pago}}
            @else
            {{$caja_linea->caja->fecha}}
            @endif        </td>

        </td>
        <td align="right"> {{ number_format($caja_linea->subtotal, 2) }} </td>
    </tr>

    @endforeach
    <tr>
        <td>
            Subtotal
        </td>
        <td></td>
        <td align="right"> {{ number_format($caja->subtotal, 2) }} </td>
    </tr>
    <tr>
        <td>
            Recargos
        </td>
        <td></td>
        <td align="right"> {{ number_format($caja->recargo, 2) }} </td>
    </tr>
    <tr>
        <td>
            Descuentos
        </td>
        <td></td>
        <td align="right"> {{ number_format($caja->descuento, 2) }} </td>
    </tr>
    <tr>
        <td>
            Total
        </td>
        <td></td>
        <td align="right"> {{ number_format($caja->total, 2) }}
        <br/>{{$totalLetra}} {{round($centavos)."/100 M.N."}} </td>
    </tr>
    <tr>
        <tr><td colspan="2">Fecha Impresion: {{$fecha}}</td></tr>
    </tr>
    <tr>
        <td>
            Pago
        </td>
        @php


        //dd($fecha);
        $lugarFecha = \Carbon\Carbon::createFromFormat('d-m-Y H:i:s', $fecha);
        //dd($lugarFecha);
        $mes = App\Mese::find($lugarFecha->month);
        $fechaLetra = $caja->plantel->municipio . ", " .
            $caja->plantel->estado . "; a " .
            $lugarFecha->day . " de " .
            $mes->name . " de " . $lugarFecha->year;
         //dd($fechaLetra);
        @endphp
        <td>Fecha Pago:{{$fechaLetra}}</td>
        <td align="right"> {{ $pago->monto }} </td>
    </tr>
    <tr>
        <td>
            Acumulado
        </td>
        <td></td>
        <td align="right"> {{ $acumulado }} </td>
    </tr>
    <tr>
        <td>
            *Pendiente
        </td>
        <td></td>
        <td align="right"> {{ $caja->total-$acumulado }} </td>
    </tr>

    <tr>
        <td colspan=3>
        <table style="width:100%;height:auto;border:1px solid #ccc;font-size: 0.70em;">
            <tr>
                @foreach($sucursales as $sucursal)
                <td>
                    {{$sucursal->razon}}<br/>
                    {{$sucursal->rfc}}<br/>
                    {{$sucursal->calle}} {{$sucursal->no_int}}, {{$sucursal->colonia}}, <br/>
                    {{$sucursal->municipio}}, {{$sucursal->estado}}, C.P. {{$sucursal->cp}}<br/>
                </td>
                @endforeach
            </tr>
        </table>
        <td>
    </tr>
    <tr><td>{{$fechaLetra}}</td></tr>
    <tr><td>*El saldo pendiente puede incrementar por recargos al exceder la fecha limite de pago</td></tr>

</table>


<table style="width:100%;height:auto;border:1px solid #ccc;font-size: 0.70em;">
    <tr>
        <td align="center" colspan="2">
            @if(isset($combinacion->especialidad->imagen))
                <img src="{{asset('storage/especialidads/'.$combinacion->especialidad->imagen)}}"
                    alt='img' style='width: 100px;
                    margin: 4px;'>
            @endif
        </td>
    </tr>
    <tr><td colspan="2" align="center" >
        @php
        $cadena='Id:'.$cliente->id.
                       '; Nombre:'.$cliente->nombre.' '.$cliente->nombre2.' '.$cliente->ape_paterno.' '.$cliente->ape_materno.
                       '; Plantel:'.$cliente->plantel->razon;
        foreach($caja->cajaLns as $caja_linea){
            if($caja_linea->cajaConcepto->id==1){
                $cadena=$cadena.';'.$caja_linea->cajaConcepto->name." (".$caja_linea->adeudo->fecha_pago.")";
            }else{
                $cadena=$cadena.';'.$caja_linea->cajaConcepto->name;
            }

        }
        $cadena=$cadena.'; Total:'.number_format($caja->total, 2).'; '.$impresion_token->toke_unico;
        @endphp

        <img src="data:image/png;base64,
                                {!! base64_encode(QrCode::format('png')->size(80)->generate($cadena_pie)) !!} ">
    </td></tr>
    <tr><td colspan="2" align="center">{{$cliente->plantel->razon}}</td></tr>
    <tr><td colspan="2" align="center">RFC: {{$cliente->plantel->rfc}}</td></tr>
    <tr>
        <td colspan="2" align="center">
            {{
                $cliente->plantel->calle." ".
                $cliente->plantel->no_int.", ".
                $cliente->plantel->no_ext.", colonia ".
                $cliente->plantel->colonia.", ".
                $cliente->plantel->municipio.", ".
                $cliente->plantel->estado.", México"
            }}
        </td>
    </tr>

    <tr>
        <td colspan="2" >

            @if($combinacion)
            Estudios:{{$combinacion->especialidad->name." / ".
                       $combinacion->nivel->name." / ".
                       $combinacion->grado->name}}
            @endif
        </td>
    </tr>

    <tr>
        <td colspan="2" >
            @if($caja->st_caja_id==1)
                Ticket {{$caja->consecutivo}} pagado el {{$caja->fecha}}
            @elseif($caja->st_caja_id==2)
                Ticket {{$caja->consecutivo}} cancelado el {{$caja->fecha}}
            @else
                Ticket {{$caja->consecutivo}} en espera de su pago
            @endif
        </td>
    </tr>

    <tr>
        <td colspan="2">
            Atendido por: {{ $empleado->nombre." ".$empleado->ape_paterno." ".$empleado->ape_materno }}
        </td>
    </tr>
    <tr>
        <td colspan="2"     >
            Alumno:{{$cliente->id."-".$cliente->nombre." ".$cliente->nombre2." ".$cliente->ape_paterno." ".$cliente->ape_materno}}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td width="50%">
            Serv. Educ.
        </td>
        <td>
            F. Limite Pago
        </td>
        <td align="right"> Monto </td>
    </tr>

    <tr>
        <td>
            Total
        </td>
        <td></td>
        <td align="right"> {{ number_format($caja->total, 2) }}
        <br/>{{$totalLetra}} {{round($centavos)."/100 M.N."}} </td>
    </tr>
    <tr>

        <tr><td colspan="2">Fecha Impresion: {{$fecha}}</td></tr>
    </tr>
    <tr>
        <td>
            Pago
        </td>
        @php


        //dd($fecha);
        $lugarFecha = \Carbon\Carbon::createFromFormat('d-m-Y H:i:s', $fecha);
        //dd($lugarFecha);
        $mes = App\Mese::find($lugarFecha->month);
        $fechaLetra = $caja->plantel->municipio . ", " .
            $caja->plantel->estado . "; a " .
            $lugarFecha->day . " de " .
            $mes->name . " de " . $lugarFecha->year;
         //dd($fechaLetra);
        @endphp
        <td>Fecha Pago:{{$fechaLetra}}</td>
        <td align="right"> {{ $pago->monto }} </td>
    </tr>
    <tr>
        <td>
            Acumulado
        </td>
        <td></td>
        <td align="right"> {{ $acumulado }} </td>
    </tr>
    <tr>
        <td>
            *Pendiente
        </td>
        <td></td>
        <td align="right"> {{ $caja->total-$acumulado }} </td>
    </tr>

    <tr>
        <td colspan=3>
        <table style="width:100%;height:auto;border:1px solid #ccc;font-size: 0.70em;">
            <tr>
                @foreach($sucursales as $sucursal)
                <td width="auto">
                {{$sucursal->razon}}<br/>
                {{$sucursal->rfc}}<br/>
                {{$sucursal->calle}} {{$sucursal->no_int}}, {{$sucursal->colonia}}, <br/>
                {{$sucursal->municipio}}, {{$sucursal->estado}}, C.P. {{$sucursal->cp}}<br/>
                </td>
                @endforeach
            </tr>
        </table>
        </td>
    </tr>
    <tr><td>*El saldo pendiente puede incrementar por recargos al exceder la fecha limite de pago</td></tr>


</table>

</div>

<script type="text/php">
    if (isset($pdf)){
        $font = $fontMetrics->getFont("Arial", "bold");
        $pdf->page_text(700, 590, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 10, array(0, 0, 0));
    }
</script>

<script>
    window.print();
</script>



</body>
</html>
