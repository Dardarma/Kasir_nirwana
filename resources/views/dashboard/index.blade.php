@extends('layouts.app')

@section('content-header')
    <h1>Dashboard</h1>
@endsection

@section('content')
    @if ($showReports)
        @php
            $money = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
            $reports = [
                'penjualan' => ['Penjualan', 'fa-cash-register', 'success', route('kasir.list'), 'kasir.view'],
                'produksi' => ['Produksi', 'fa-industry', 'primary', route('produksi.list'), 'produksi.view'],
                'pembelian' => ['Pengeluaran', 'fa-receipt', 'danger', route('pembelian.list'), 'pembelian.view'],
            ];
        @endphp

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('home') }}" class="form-row align-items-end">
                    <div class="form-group col-sm-4 col-lg-3 mb-2">
                        <label for="periode">Periode</label>
                        <select class="custom-select" id="periode" name="periode">
                            @foreach ($periods as $key => $label)
                                <option value="{{ $key }}" @selected($period === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-sm-4 col-lg-3 mb-2">
                        <label for="tanggal">Tanggal acuan</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-control" value="{{ $date->toDateString() }}" required>
                    </div>
                    <div class="col-sm-auto mb-2">
                        <button class="btn btn-primary" type="submit">Tampilkan laporan</button>
                    </div>
                </form>
                @foreach ($errors->all() as $error)
                    <p class="text-danger mb-1">{{ $error }}</p>
                @endforeach
                <p class="text-muted mt-2 mb-0">
                    {{ $start->locale('id')->isoFormat('D MMMM YYYY') }}
                    @if (!$start->isSameDay($end))
                        – {{ $end->locale('id')->isoFormat('D MMMM YYYY') }}
                    @endif
                </p>
            </div>
        </div>

        <div class="row">
            @foreach ($reports as $type => [$title, $icon, $color, $url, $permission])
                <div class="col-md-4 d-flex">
                    <section class="card card-outline card-{{ $color }} flex-fill">
                        <div class="card-body">
                            <h2 class="h6 text-muted"><i class="fas {{ $icon }} mr-2" aria-hidden="true"></i>{{ $title }}</h2>
                            <p class="h4 mb-1">
                                {{ $type === 'produksi' ? format_jumlah($totals[$type]['jumlah']) . ' produksi' : $money($totals[$type]['nominal']) }}
                            </p>
                            @if ($type !== 'produksi')
                                <p class="text-muted mb-0">{{ $totals[$type]['jumlah'] }} transaksi {{ $type }}</p>
                            @endif
                        </div>
                        @if ($features->has($permission))
                        <div class="card-footer bg-white">
                            <a href="{{ $url }}">Riwayat {{ $type }} <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i></a>
                        </div>
                        @endif
                    </section>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-xl-8">
                <section class="card">
                    <div class="card-header"><h2 class="card-title">Rincian per hari</h2></div>
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col" class="text-right">Penjualan</th>
                                    <th scope="col" class="text-right">Jumlah produksi</th>
                                    <th scope="col" class="text-right">Pengeluaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($days as $day)
                                    <tr>
                                        <th scope="row" class="text-nowrap font-weight-normal">{{ $day['tanggal'] }}</th>
                                        <td class="text-right text-nowrap">{{ $money($day['values']['penjualan']['nominal']) }}</td>
                                        <td class="text-right">{{ format_jumlah($day['values']['produksi']['jumlah']) }}</td>
                                        <td class="text-right text-nowrap">{{ $money($day['values']['pembelian']['nominal']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <section class="card">
                    <div class="card-header"><h2 class="card-title">Hasil produksi per barang</h2></div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Barang</th>
                                    <th scope="col" class="text-right">Hasil</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr>
                                        <th scope="row" class="font-weight-normal">{{ $product->nama_barang }}</th>
                                        <td class="text-right">{{ format_jumlah($product->jumlah) }} {{ $product->satuan }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-muted">Belum ada hasil produksi pada periode ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>

    @endif

    <nav class="mb-3" aria-label="Menu operasional">
        @forelse ($features as [$label, $url])
            <a class="btn btn-outline-secondary mb-1" href="{{ $url }}">{{ $label }}</a>
        @empty
            @unless ($showReports)
                <p class="text-muted">Belum ada fitur yang tersedia untuk akun ini.</p>
            @endunless
        @endforelse
    </nav>
@endsection
