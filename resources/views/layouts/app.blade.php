<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nirwana</title>
    <script>
        const quantityFormatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });

        function formatJumlah(value) {
            const number = Number(value);
            return Number.isFinite(number) ? quantityFormatter.format(number) : '–';
        }

        function isiPilihanSatuan(select, satuanBarang, conversions, selected = satuanBarang) {
            const base = conversions[satuanBarang]?.dasar;
            select.replaceChildren(new Option('Pilih satuan', ''));
            for (const [unit, conversion] of Object.entries(conversions)) {
                if (conversion.dasar === base) select.add(new Option(unit, unit));
            }
            select.value = Array.from(select.options).some(option => option.value === selected) ? selected : '';
        }

        function escapeHtmlText(value) {
            const element = document.createElement('span');
            element.textContent = value;
            return element.innerHTML;
        }
    </script>


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
            <img class="animation__shake" src="{{ asset('dist/img/AdminLTELogo.png') }}" alt="" height="60"
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
            <a href="{{ route('home') }}" class="brand-link" aria-label="Nirwana">
                <img src="{{ asset('dist/img/AdminLTELogo.png') }}" alt="" class="brand-image img-circle elevation-3"
                    width="33" height="33" style="opacity: .8">
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
                        @php
                            $user = auth()->user();
                            $sidebarGroups = [
                                ['Master Data', 'fa-tachometer-alt', [
                                    ['barang.view', 'Barang', url('/barang'), 'barang'],
                                    ['barang.view', 'Master Satuan', route('satuan.index'), 'satuan*'],
                                    ['sub-barang.view', 'Sub Barang', route('sub_barang.index'), 'sub-barang'],
                                    ['customers.view', 'Pelanggan', route('customers.index'), 'customers*'],
                                    ['users.view', 'User', route('users.index'), 'users*'],
                                    ['roles.manage', 'Role & Permission', route('roles.index'), 'roles*'],
                                    ['roles.manage', 'Pengaturan Permission', route('permissions.index'), 'permissions*'],
                                ]],
                                ['Transaksi', 'fa-dollar-sign', [
                                    ['kasir.view', 'Kasir', route('kasir.index'), 'kasir*'],
                                    ['pembelian.view', 'Pembelian', route('pembelian.index'), 'pembelian*'],
                                    ['pemakaian.view', 'Pemakaian', route('pemakaian.index'), 'pemakaian*'],
                                    ['produksi.view', 'Produksi', url('/produksi'), 'produksi*'],
                                    ['stock.view', 'Stok', route('stok.index'), 'stok'],
                                    ['laba-rugi.view', 'Laba Rugi', route('stok.laba_rugi'), 'laba-rugi'],
                                ]],
                            ];
                            $homeLabel = $user->hasPermission('dashboard.view') ? 'Dashboard' : 'Beranda';
                        @endphp

                        <li class="nav-item">
                            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-home"></i>
                                <p>{{ $homeLabel }}</p>
                            </a>
                        </li>

                        @foreach ($sidebarGroups as [$group, $icon, $items])
                            @php
                                $allowedItems = collect($items)->filter(fn ($item) => $user->hasPermission($item[0]));
                                $groupActive = $allowedItems->contains(fn ($item) => request()->is($item[3]));
                            @endphp

                            @if ($allowedItems->isNotEmpty())
                                <li class="nav-item {{ $groupActive ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ $groupActive ? 'active' : '' }}">
                                        <i class="nav-icon fas {{ $icon }}"></i>
                                        <p>{{ $group }} <i class="right fas fa-angle-left"></i></p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @foreach ($allowedItems as [, $label, $url, $path])
                                            <li class="nav-item">
                                                <a href="{{ $url }}" class="nav-link {{ request()->is($path) ? 'active' : '' }}">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p>{{ $label }}</p>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endforeach

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
