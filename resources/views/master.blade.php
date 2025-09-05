<!DOCTYPE html>

<html>



<head>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>MoniForm | @yield('title')</title>




    <!-- Tell the browser to be responsive to screen width -->

    <meta name="viewport" content="width=device-width, initial-scale=1">

    @yield('css')

    <!-- Font Awesome -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">



    <link rel="stylesheet" href="{{asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css')}}">

    <!-- Ionicons -->

    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

    <!-- DataTables -->

    <link rel="stylesheet" href="{{asset('vendor/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">

    <link rel="stylesheet" href="{{asset('vendor/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">

    <!-- Theme style -->

    <link rel="stylesheet" href="{{asset('vendor/adminlte/dist/css/adminlte.min.css')}}">

    <link rel="stylesheet" href="{{asset('vendor/adminlte/plugins/toastr/toastr.min.css')}}">



    <!-- Google Font: Source Sans Pro -->

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.css"
        integrity="sha512-uHuCigcmv3ByTqBQQEwngXWk7E/NaPYP+CFglpkXPnRQbSubJmEENgh+itRDYbWV0fUZmUz7fD/+JDdeQFD5+A=="

        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>

        .loader {

            width: 48px;

            height: 48px;

            border: 5px solid #FFF;

            border-bottom-color: #124170;

            border-radius: 50%;

            display: inline-block;

            box-sizing: border-box;

            animation: rotation 1s linear infinite;

        }



        @keyframes rotation {

            0% {

                transform: rotate(0deg);

            }



            100% {

                transform: rotate(360deg);

            }

        }



        .loader1 {

            width: 48px;

            height: 48px;

            border: 5px solid #FFF;

            border-bottom-color: #124170;

            border-radius: 50%;

            display: inline-block;

            box-sizing: border-box;

            animation: rotation 1s linear infinite;

        }



        @keyframes rotation {

            0% {

                transform: rotate(0deg);

            }



            100% {

                transform: rotate(360deg);

            }

        }



        .br {

            border: 0px solid red;

        }



        .bgmer {

            background-color: #b92c10 !important;

            color: white !important;

            border: 1px solid #EB4E2D !important;

        }



        .bghit {

            background-color: #141414 !important;

            color: white !important;

            border: 1px solid #141414 !important;

        }



        .page-item.active .page-link {

            color: #fff !important;

            background-color: #000 !important;

            border-color: #000 !important;

        }



        .page-link {

            color: #000 !important;

            background-color: #fff !important;

            border: 1px solid #dee2e6 !important;

        }



        .page-link:hover {

            color: #fff !important;

            background-color: #000 !important;

            border-color: #000 !important;

        }

    </style>


</head>



<body class="hold-transition sidebar-mini">



    <div class="wrapper">

        <!-- Navbar -->

        @include('navbar')

        <!-- /.navb ar -->



        @include('sidebar')

        <!-- Content Wrapper. Contains page content -->

        <div class="content-wrapper">

            <!-- Content Header (Page header) -->

            <section class="content-header">

                <div class="container-fluid">



                </div><!-- /.container-fluid -->

            </section>



            <!-- Main content -->

            <section class="content">

                <div class="row">

                    <div class="col-12">



                        {{-- @if ($errors->any())

                        <div class="alert alert-danger d-flex align-items-center" role="alert">

                            <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:">

                                <use xlink:href="#exclamation-triangle-fill"></use>

                            </svg>

                            <ul>

                                @foreach ($errors->all() as $error)

                                <div>

                                    <li>{{ $error }}</li>

                    </div>



                    @endforeach

                    </ul>

                </div>

                @endif



                @if(Session('sukses'))



                <div class="alert alert-success alert-dismissible">

                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>

                    <h5><i class="icon fas fa-check"></i> Berhasil!</h5>

                    {{Session::get('sukses')}}



                </div>



                @endif --}}



                @yield('content')



                <!-- /.card -->

        </div>

        <!-- /.col -->

    </div>

    <!-- /.row -->

    </section>

    <!-- /.content -->

    </div>

    <!-- /.content-wrapper -->

   <footer class="main-footer text-right">
        <div class="float-left d-none d-sm-block">
            <b>Version</b> 1.0.1
        </div>
        <strong>&copy; 2025 <a href="/">MoniForm</a></strong>
    </footer>




    <!-- Control Sidebar -->

    <aside class="control-sidebar control-sidebar-dark">

        <!-- Control sidebar content goes here -->

    </aside>

    <!-- /.control-sidebar -->

    </div>

    <!-- ./wrapper -->







    <!-- jQuery -->

    <script src="{{asset('vendor/adminlte/plugins/jquery/jquery.min.js')}}"></script>

    <!-- Bootstrap 4 -->

    <script src="{{asset('vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

    <!-- DataTables -->

    <script src="{{asset('vendor/adminlte/plugins/datatables/jquery.dataTables.min.js')}}"></script>

    <script src="{{asset('vendor/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>

    <script src="{{asset('vendor/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>

    <script src="{{asset('vendor/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>

    <!-- AdminLTE App -->

    <script src="{{asset('vendor/adminlte/dist/js/adminlte.js')}}"></script>

    <script src="{{asset('vendor/adminlte/plugins/sweetalert2/sweetalert2.min.js')}}"></script>

    @notifyJs



    @yield('js')







</body>



</html>