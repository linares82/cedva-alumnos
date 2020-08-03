<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Cedva Alumnos</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            #Linea_top {
                width: 100%;
                float: left;
                height: 5px;
                background-color: #e5322d;
            }

            #logo_01 {

                float: left;
                box-sizing: border-box;
                padding: 10px 20px;
                width:50%;
            }

            #logo_02 {

                float: left;
                box-sizing: border-box;
                padding: 10px 20px;
                text-align: right;
                margin-top: 50px;
                -ms-display: flex;
                display: flex;
                align-items: flex-end;
                justify-content: flex-end;
                width:50%;
            }

            #Top {
                width: 100%;
                float: left;
                min-height: 60px;
                border-bottom: 1px solid #e5322d;
            }
            #Contenedor_top {
                width: 100%;
                margin: 0 auto;
                max-width: 1240px;
                box-sizing: border-box;
            }

            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .subtitle {
                font-size: 42px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Entrar</a>

                    @endauth
                </div>
            @endif


            <div class="content">
                <div id="Linea_top"></div>
                <section id="Contenedor_gral">
                    <article id="Contenedor_top">
                    <header id="Top">
                    <div id="logo_01" ><img src="{{ asset('img/logo1-portal.svg') }}" alt="Cedva" width="150px"></div>
                    <div id="logo_02" ><img src="{{ asset('img/logo2-portal.jpg') }}" alt="Cedva" width="150px"></div>
                    </header>
                    </article>
                </section>

                <div class="title m-b-md">
                    Portal Alumnos
                </div>
                <div class="subtitle m-b-md">
                    Bienvenido
                </div>

                <div class="links">
                    <p><a href="mailto:contacto@cedva-alumni.com.mx">contacto@cedva-alumni.com.mx</a>  ||  <a href="tel:01800 890 0855">01800 890 0855</a>  || Aviso de Privacidad  ||  CEDVA © 2020. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </body>
</html>
