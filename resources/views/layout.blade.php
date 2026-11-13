<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nirwana</title>


    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet"
        href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <style>
        :root {
            --form-control-height: calc(2.25rem + 2px);
        }

        select.form-control:not([size]):not([multiple]) {
            height: var(--form-control-height);
            padding-top: .375rem;
            padding-bottom: .375rem;
            line-height: 1.5;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-selection--single {
            height: var(--form-control-height);
            padding: 0;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            box-sizing: border-box;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            height: 100%;
            padding: .375rem 2rem .375rem .75rem;
            line-height: 1.5;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            box-sizing: border-box;
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            top: 0;
            right: .25rem;
            height: 100%;
        }

        .select2-results__option {
            line-height: 1.35;
            padding: .5rem .75rem;
            white-space: normal;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/app-responsive.css') }}">
    @yield('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60"
                width="60">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>

            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item d-flex align-items-center mr-2 navbar-user-name">
                    <span class="text-muted"><i class="fas fa-user mr-1"></i>{{ auth()->user()->name }}</span>
                </li>
                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-link nav-link" title="Keluar">
                            <i class="fas fa-sign-out-alt"></i> <span class="logout-label">Keluar</span>
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="brand-link">
                <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                <span class="brand-text font-weight-light">Nirwana</span>
            </a>

            <!-- Sidebar -->
            <div class= "sidebar">
                <div class="form-inline mt-3">
                    <div class="input-group" data-widget="sidebar-search">
                        <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                            aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-sidebar">
                                <i class="fas fa-search fa-fw"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item {{ request()->is('home') ? 'active' : '' }}">
                            <a href=" {{ url('/') }} "
                                class="nav-link {{ request()->is('home') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-home"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>
                        <li
                            class="nav-item {{ request()->is('barang', 'sub-barang', 'customers*', 'users*', 'roles*', 'permissions*') ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ request()->is('barang', 'sub-barang', 'customers*', 'users*', 'roles*', 'permissions*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Master Data
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('/barang') }}"
                                        class="nav-link {{ request()->is('barang') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Barang</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/sub-barang') }}"
                                        class="nav-link {{ request()->is('sub-barang') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Sub Barang</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('customers.index') }}"
                                        class="nav-link {{ request()->is('customers*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Pelanggan</p>
                                    </a>
                                </li>
                                @if (auth()->user()->hasPermission('users.view'))
                                    <li class="nav-item">
                                        <a href="{{ route('users.index') }}"
                                            class="nav-link {{ request()->is('users*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>User</p>
                                        </a>
                                    </li>
                                @endif
                                @if (auth()->user()->hasPermission('roles.manage'))
                                    <li class="nav-item">
                                        <a href="{{ route('roles.index') }}"
                                            class="nav-link {{ request()->is('roles*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Role & Permission</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('permissions.index') }}"
                                            class="nav-link {{ request()->is('permissions*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Pengaturan Permission</p>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        <li
                            class="nav-item {{ request()->is('kasir','pembelian','pemakaian','produksi','stok') ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ request()->is('kasir','pembelian','pemakaian','produksi','stok') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-dollar-sign"></i>
                                <p>
                                    Transaksi
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('/kasir') }}"
                                        class="nav-link {{ request()->is('kasir') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Kasir</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/pembelian') }}"
                                        class="nav-link {{ request()->is('pembelian') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Pembelian</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/pemakaian') }}"
                                        class="nav-link {{ request()->is('pemakaian') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Pemakaian</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/produksi') }}"
                                        class="nav-link {{ request()->is('produksi') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Produksi</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('stok.index') }}"
                                        class="nav-link {{ request()->is('stok') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Stok</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                {{-- @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif --}}
                @yield('content-header')
            </div>

            <section class="content">
                <div class="container-fluid">
                    <!-- Main row -->
                    <div class="row">
                        <div class="col-md-12">

                            @yield('content')
                        </div>
                    </div>
                    <!-- /.row (main row) -->
                </div>
                <!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- daterangepicker -->
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('dist/js/adminlte.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
    @yield('script')

    <script>
        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        const flashToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        })

        @if (session('success'))
            flashToast.fire({
                icon: 'success',
                title: @json(session('success'))
            })
        @endif

        @if (session('error'))
            flashToast.fire({
                icon: 'error',
                title: @json(session('error'))
            })
        @endif

        @if ($errors->any())
            flashToast.fire({
                icon: 'error',
                title: 'Validasi gagal',
                html: @json(implode('<br>', $errors->all()))
            })
        @endif
    </script>
</body>

</html>
