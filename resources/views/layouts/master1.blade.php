<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta charset="utf-8" />
        <title>{{ config('app.name', 'Laravel') }}</title>

        <meta name="description" content="" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0, maximum-scale=1.0"
        />

        <!-- bootstrap & fontawesome -->
        <link rel="stylesheet" href="{{ asset('ace-master/assets/css/bootstrap.min.css') }}" />
        <!--<link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">-->
        <link rel="stylesheet" href="{{ asset('ace-master/assets/font-awesome/4.5.0/css/font-awesome.min.css') }}" />

        <!-- page specific plugin styles -->

        <!-- text fonts -->
        <link rel="stylesheet" href="{{ asset('ace-master/assets/css/fonts.googleapis.com.css') }}" />

        <!-- ace styles -->
        <link rel="stylesheet" href="{{ asset('ace-master/assets/css/ace.min.css') }}" class="ace-main-stylesheet" id="main-ace-style" />

        <!--[if lte IE 9]>
                <link rel="stylesheet" href="assets/css/ace-part2.min.css" class="ace-main-stylesheet" />
        <![endif]-->
        <link rel="stylesheet" href="{{ asset('ace-master/assets/css/ace-skins.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('ace-master/assets/css/ace-rtl.min.css') }}" />

        <!--[if lte IE 9]>
            <link
                rel="stylesheet"
                href="assets/css/ace-part2.min.css"
                class="ace-main-stylesheet"
            />
        <![endif]-->

        <!--[if lte IE 9]>
            <link rel="stylesheet" href="assets/css/ace-ie.min.css" />
        <![endif]-->

        <!-- inline styles related to this page -->

        <!-- ace settings handler -->
        <script src="assets/js/ace-extra.min.js"></script>

        <!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->

        <!--[if lte IE 8]>
            <script src="assets/js/html5shiv.min.js"></script>
            <script src="assets/js/respond.min.js"></script>
        <![endif]-->
        <!-- Colorpicker-->
        <link rel="stylesheet" href="{{ asset('ace-master/assets/css/bootstrap-colorpicker.min.css') }}" />
        <!--Editable-->
        <link rel="stylesheet" href="{{asset('ace-master/assets/css/bootstrap-editable.min.css')}}" />
        <!--Chosen-->
        <link rel="stylesheet" href="{{asset('ace-master/assets/css/chosen.min.css')}}" type="text/css" />
        <!--ToAStr-->
        <link rel="stylesheet" href="{{asset('ace-master/assets/css/toastr.css')}}" type="text/css" />
        <!--Datetime picker-->
        <link rel="stylesheet" href="{{asset('ace-master/assets/css/bootstrap-datetimepicker.min.css')}}" />
        <link rel="stylesheet" href="{{asset('ace-master/assets/css/bootstrap-datepicker3.min.css')}}" />

        <style>
            .navbar {
            margin: 0;
            padding-left: 0;
            padding-right: 0;
            border-width: 0;
            border-radius: 0;
            -webkit-box-shadow: none;
            box-shadow: none;
            min-height: 45px;
            background: red;
            }

            @media only screen
            .navbar.navbar-collapse .navbar-container {
                background-color: #ff0000;
            }
        </style>
    </head>

    <body class="no-skin">
        <div
            id="navbar"
            class="navbar navbar-default navbar-collapse ace-save-state"
        >
            <div class="navbar-container ace-save-state" id="navbar-container">

                <button
                    type="button"
                    class="navbar-toggle menu-toggler pull-left"
                    id="menu-toggler"
                    data-target="#sidebar"
                >
                    <span class="sr-only">Toggle sidebar</span>

                    <span class="icon-bar"></span>

                    <span class="icon-bar"></span>

                    <span class="icon-bar"></span>
                </button>

                <div class="navbar-header pull-left">
                    <a href="index.html" class="navbar-brand">
                        <small>
                            <i class="fa fa-users"></i>
                            {{ config('app.name', 'Laravel') }}
                        </small>
                    </a>

                    <button
                        class="pull-right navbar-toggle navbar-toggle-img collapsed"
                        type="button"
                        data-toggle="collapse"
                        data-target=".navbar-buttons"
                    >
                        <span class="sr-only">Toggle user menu</span>


                    </button>
                </div>




                <div class="navbar-buttons navbar-header pull-right" role="navigation">
                    <ul class="nav ace-nav">

                        @guest
                        <li><a href="{{ route('login') }}">Entrar</a></li>
                        @else
                        <li class="red">
                            <a  href="#" >
                                <span class="user-info">
                                    <small>Bienvenido,</small>
                                    @php
                                    $cliente=App\Cliente::where('matricula',Auth::user()->name)->first();
                                    @endphp
                                    {{ $cliente->nombre }} {{ $cliente->nombre2 }}
                                </span>

                            </a>

                        </li>

                        <li class="red">
                            <a href="{{ route('users.editPerfil', Auth::user()->id) }}">
                                <i class="ace-icon fa fa-user"></i>
                                Perfil
                            </a>
                        </li>
                        <li class="red">
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                       document.getElementById('logout-form').submit();">
                                Salir
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div><!-- /.navbar-container -->
        </div>

        <div class="main-container ace-save-state" id="main-container">
            <script type="text/javascript">
                try {
                    ace.settings.loadState('main-container')
                } catch (e) {
                }
            </script>

            <div class="main-content">
                <div class="main-content-inner">
                    <script type="text/javascript">
                        try{ace.settings.loadState('sidebar')}catch(e){}
                    </script>

                    <div id="sidebar" class="sidebar h-sidebar navbar-collapse collapse ace-save-state" data-sidebar="true" data-sidebar-scroll="true" data-sidebar-hover="true">
                        <ul class="nav nav-list">
                            <!--<li class="hover">
                                <a href="#">
                                    <i class="menu-icon fa fa-tachometer"></i>
                                    <span class="menu-text"> Tablero Principal </span>
                                </a>

                                <b class="arrow"></b>
                            </li>-->

                            <li class="hover">
                                <a href="{{ route('fichaAdeudos.datosFiscales') }}">
                                    <i class="menu-icon fa fa-user"></i>
                                    <span class="menu-text">Datos Fiscales</span>
                                </a>

                                <b class="arrow"></b>
                            </li>

                            <li class="hover">
                                <a href="{{ route('fichaAdeudos.index') }}">
                                    <i class="menu-icon fa fa-list-alt"></i>
                                    <span class="menu-text">Ficha Pagos / Adeudos</span>
                                </a>

                                <b class="arrow"></b>
                            </li>



                        </ul><!-- /.nav-list -->
                    </div>


                    <div class="main-content-inner">

                        <!-- PAGE CONTENT BEGINS -->
                        <div class="col-xs-12">
                        @yield('content')
                        </div>
                        <!-- PAGE CONTENT ENDS -->
                    </div>


                </div>
            </div><!-- /.main-content -->

            <div class="footer">
                <div class="footer-inner">
                    <div class="footer-content">
                        <span class="bigger-60">
                            Derechos reservados 2019, {{ config('app.name', 'Laravel') }}
                        </span>

                    </div>
                </div>
            </div>

            <a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
                <i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
            </a>
        </div><!-- /.main-container -->

        <!-- basic scripts -->

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}"></script>

        <!--[if !IE]> -->
        <script src="{{ asset('ace-master/assets/js/jquery-2.1.4.min.js') }}"></script>

        <!-- <![endif]-->

        <!--[if IE]>
<script src="assets/js/jquery-1.11.3.min.js"></script>
<![endif]-->
        <script type="text/javascript">
                    if ('ontouchstart' in document.documentElement)
                        document.write("<script src='" + "{{ asset('ace-master/assets/js/jquery.mobile.custom.min.js') }}" + "'>" + "<" + "/script>");
        </script>
        <script src="{{ asset('ace-master/assets/js/bootstrap.min.js') }}"></script>
        <!--<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>-->

        <!-- page specific plugin scripts -->

        <!--[if lte IE 8]>
          <script src="assets/js/excanvas.min.js"></script>
        <![endif]-->
        <script src="{{ asset('ace-master/assets/js/jquery-ui.custom.min.js') }}"></script>
        <script src="{{ asset('ace-master/assets/js/jquery.ui.touch-punch.min.js') }}"></script>
        <script src="{{ asset('ace-master/assets/js/jquery.easypiechart.min.js') }}"></script>
        <script src="{{ asset('ace-master/assets/js/jquery.sparkline.index.min.js') }}"></script>


        <!-- ace scripts -->
        <script src="{{ asset('ace-master/assets/js/ace-elements.min.js') }}"></script>
        <script src="{{ asset('ace-master/assets/js/ace.min.js') }}"></script>

        <!--Colorpicker-->
        <script src="{{ asset('ace-master/assets/js/bootstrap-colorpicker.min.js') }}"></script>
        <!--Editable-->
        <script src="{{ asset('ace-master/assets/js/bootstrap-editable.min.js') }}"></script>
        <!--        Chosen-->
        <script src="{{ asset('ace-master/assets/js/chosen.jquery.min.js') }}"></script>
        <!--ToAStr-->
        <script src="{{ asset('ace-master/assets/js/toastr.js') }}"></script>
        <!--Moment for datetime picker-->
        <script src="{{ asset('ace-master/assets/js/moment.min.js') }}"></script>
        <!--Datetime picker-->
        <script src="{{ asset('ace-master/assets/js/bootstrap-datetimepicker.min.js') }}"></script>
        <!-- Date picker-->
        <script src="{{ asset('ace-master/assets/js/bootstrap-datepicker.min.js') }}"></script>
        <!--Modales con bootobox-->
        <script src="{{ asset('ace-master/assets/js/bootbox.js') }}" ></script>

        <script type="text/javascript">
            $("button[type=submit]").click(function(){
                $(this).prop('disabled', true);
                //$(this).closest('form').submit();
                //$('#formulario').submit();
            });
            $(document).ready(function () {
                $("#search_form").hide();
                $("#search_btn").click(function () {
                    $("#search_form").toggle(1000);
                });
                $(".chosen").chosen({width: "100%"});

                //aplica clases para el menu
                let optSelected=$('.active');

            });
            $('.date-picker').datepicker({
                    autoclose: true,
                    todayHighlight: true
            })
            //show datepicker when clicking on the icon
            .next().on(ace.click_event, function(){
                    $(this).prev().focus();
            });
        </script>
        <!-- inline scripts related to this page -->
        @stack('scripts')

    </body>
</html>
