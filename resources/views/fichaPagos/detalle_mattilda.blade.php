@extends('layouts.master1')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h1>Datos para el pago</h1>
        </div>
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
                        {{ date('d-m-Y', strtotime($adeudo_pago_online->created_at)) }}
                    </div>
                </div>
                <div class="profile-info-row">
                    <div class="profile-info-name"> F. Vencimiento (dd-mm-yyyy) </div>
                    <div class="profile-info-value">
                        {{ date('d-m-Y', strtotime($adeudo_pago_online->fecha_limite)) }}
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
            @if (!is_null($peticionMattilda))
                @if($peticionMattilda->rmethod=="card")
                <div class="col-sm-8 col-sm-offset-2">
                    <div class="widget-box">
                        <div class="widget-header">
                            <h5 class="widget-title">Se detectaron uno o mas intentos de pago</h5>
                        </div>
                        <div class="widget-body">
                            <div class="widget-main">
                                Sugerimos revisar su cuenta, para evitar un doble cargo o reportar a su escuela para revision.
                            </div>
                        </div>
                    </div>
                </div>
                @elseif($peticionMattilda->rmethod=="oxxo")
                <div class="col-sm-8 col-sm-offset-2">
                    <div class="widget-box">
                        <div class="widget-header">
                            <h5 class="widget-title">Se detectaron uno o mas intentos de pago</h5>
                        </div>
                        <div class="widget-body">
                            <div class="widget-main">
                                Si ya realizo su pago correspondiente en una sucursal Oxxo, actualice su
                                informacion haciendo click
                                <a href="{{ route('fichaAdeudos.buscarMattilda',
                                    array('plantel_id'=>$plantel->id,
                                    'adeudo_pago_online_id'=>$adeudo_pago_online->id,
                                    'peticion_mattilda_id'=>$peticionMattilda->id))}} ">
                                    Aqui
                                </a>
                                .
                            </div>
                        </div>
                    </div>
                </div>
                @elseif($peticionMattilda->rmethod=="spei")
                <div class="col-sm-8 col-sm-offset-2">
                    <div class="widget-box">
                        <div class="widget-header">
                            <h5 class="widget-title">Se detectaron uno o mas intentos de pago</h5>
                        </div>
                        <div class="widget-body">
                            <div class="widget-main">
                                Si ya realizo su pago correspondiente por SPEI, actualice su
                                informacion haciendo click
                                <a href="{{ route('fichaAdeudos.buscarMattilda',
                                    array('plantel_id'=>$plantel->id,
                                    'adeudo_pago_online_id'=>$adeudo_pago_online->id,
                                    'peticion_mattilda_id'=>$peticionMattilda->id))}} ">
                                    Aqui
                                </a>
                                .
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endif
        </div>
        <div class="col-md-12">
            <form action="/checkout" id="payment-form">
                @csrf
                <div class="container"></div>
                <input type="submit" />
            </form>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.mattilda.gr4vy.app/embed.latest.js"></script>
    <!--Alternativa https://cdn.mattilda.gr4vy.app/embed.latest.js
    https://cdn.mattildapayments.com/embed.latest.js
    -->
    <script type="text/javascript">

        //const token = @json($token);

        /*if (!window.gr4vy) {
          console.error('Error: gr4vy no está disponible.');
        } else {
            const embed = new gr4vy.Embed('mattilda', {
                merchantAccountId: 'cedva', // Reemplaza con el tuyo
                form: '#payment-form',
                amount: {{ $adeudo_pago_online->total * 100 }},        // en centavos (12.99 MXN)
                currency: 'MXN',
                country: 'MX',
                token: "{{ $token }}",
                environment: 'sandbox', // Cambiar a 'production' al salir
                onEvent: (name, payload) => {
                    console.log('Evento:', name, payload);
                    // Ejemplo: si quieres actuar cuando se crea la transacción
                    if (name === 'transactionCreated') {
                        console.log('Transacción creada:', payload);
                    }
                },
                onComplete: (transaction) => {
                    console.log(transaction); //por ahora solo mandamos al log para revisar la
                                            // estructura cuando se obtenga un resultado
                }
            });

            // Montar el embed en el div
            embed.mount('#embed-container');
        }*/

        //Aqui solo se percibe el uso del token generado en la rutina del backend
        //pero como se enlaza el checkout session que ya deberia tener el monto?
        gr4vy.setup({
            gr4vyId: "mattilda",
            element: ".container",
            form: "#payment-form",
            amount: {{ $monto }}, //en centavos
            currency: "MXN", //Para este caso es default, lo dejamos fijo
            country: "MX", //Para este caso es default, lo dejamos fijo
            locale: 'es-MX',
            token: "{{ $token }}", //Viene del proceso anterior
            environment: "{{ $mattildaAmbiente }}", //valores posibles sandbox/production?
            merchantAccountId: "cedva",
            intent: "capture",
            onBeforeTransaction: async () => {
                // Optionally fetch or compute your external identifier (e.g. from backend)
                return {
                externalIdentifier: "{{ $peticionMattilda->id }}",
                buyerExternalIdentifier: "{{ $adeudo_pago_online->cliente_id }}",
                // you could also modify metadata, token, etc.
                };
            },
            onComplete: (transaction) => {
                $data={
                    transaction_id: transaction.id,
                    amount: transaction.amount,
                    status: transaction.status,
                    approvalUrl: transaction.paymentMethod.approvalUrl,
                    method: transaction.paymentMethod.method,
                    details:transaction.paymentMethod.details,
                    label:transaction.paymentMethod.label,
                    adeudo_pago_online:{{ $adeudo_pago_online->id }}
                }


                // estructura cuando se obtenga un resultado
                if(transaction.paymentMethod.approvalUrl !== null){
                    window.open(transaction.paymentMethod.approvalUrl, '_blank');
                }

                if(transaction.status==="processing" || transaction.status==="capture_succeeded"){
                    completarCaja($data);
                    //
                }
            },
            /*onEvent: (name, payload) => {
                        console.log('Evento:', name, payload);
                        // Ejemplo: si quieres actuar cuando se crea la transacción
                        if (name === 'transactionCreated') {
                            console.log('Transacción creada:', payload);
                        }
                    },*/
        });

        async function completarCaja(transaction){
            const completarCaja = await fetch("{{ route('fichaAdeudos.crearCajaPagoPeticionMattilda') }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(transaction),
                })
                .then(data => {
                    //console.log(data.url);
                    //alert(data.url);
                    alert('Si tu pago no es con tarjeta, cuando lo hallas realizado debes volver a esta seccion.');
                    window.location.replace("{{route('fichaAdeudos.index')}}");
                    //window.location.replace(data.url);
                });
        }
    </script>
@endpush
