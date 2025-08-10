@extends('layout')
@section('content')
    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <script>
            // Auto hide after 2 seconds
            setTimeout(function() {
                var alert = document.getElementById('success-alert');
                if (alert) {
                    alert.classList.remove('show');
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 150); // Wait for fade transition
                }
            }, 2000);
        </script>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <script>
            // Auto hide after 2 seconds
            setTimeout(function() {
                var alert = document.getElementById('error-alert');
                if (alert) {
                    alert.classList.remove('show');
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 150); // Wait for fade transition
                }
            }, 2000);
        </script>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h3 class="card-title mb-0">
                        Data Transaksi Kasir
                    </h3>
                </div>
                <div class="col-md-8">
                    <div class="d-flex justify-content-end align-items-center flex-wrap gap-2">
                        <!-- Form Search & Pagination -->
                        <form method="GET" action="{{ url('/kasir/list') }}" class="d-flex align-items-center gap-2">

                            <!-- Show entries -->
                            <div class="input-group input-group-sm mx-1" style="width: 80px;">
                                <select class="custom-select" name="paginate" onchange="this.form.submit()">
                                    <option value="10" {{ request('paginate') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('paginate') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('paginate') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('paginate') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>

                            {{-- filter date range --}}
                            <div class="input-group input-group-sm mx-1" style="width: 220px;">
                                <input type="date" name="tanggal_dari" class="form-control" placeholder="Dari tanggal" value="{{ request('tanggal_dari') }}">
                                <span class="mx-1">s/d</span>
                                <input type="date" name="tanggal_sampai" class="form-control" placeholder="Sampai tanggal" value="{{ request('tanggal_sampai') }}">
                            </div>

                            <!-- Search -->
                            <div class="input-group input-group-sm" style="width: 200px;">
                                <input type="text" name="table_search" class="form-control"
                                    placeholder="Cari transaksi..." value="{{ request('table_search') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Add Button -->
                        <a href="{{ url('/kasir') }}" class="btn btn-primary btn-sm mx-1">
                            Transaksi Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <div class="table-wrapper" style="overflow-x: auto; border-radius: 10px;">
                    <table id="data" class="table table-bordered table-hover"
                        style="border-radius: 10px; min-width: 900px;">
                        <thead style="background-color: #578FCA; color: white;">
                            <tr>
                                <th style="min-width: 60px; white-space: nowrap;">No</th>
                                <th style="min-width: 150px; white-space: nowrap;">Kode Transaksi</th>
                                <th style="min-width: 80px; white-space: nowrap;">Nama Customer</th>
                                <th style="min-width: 100px; white-space: nowrap;">Tanggal</th>
                                <th style="min-width: 80px; white-space: nowrap;">Total</th>
                                <th style="min-width: 80px; white-space: nowrap;">Status</th>
                                <th style="min-width: 120px; white-space: nowrap;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($transaksi) == 0)
                                <tr>
                                    <td colspan="6" class="text-center">Data not found</td>
                                </tr>
                            @endif
                            @foreach ($transaksi as $key => $item)
                                <tr>
                                    <td style="white-space: nowrap;">{{ $key + 1 }}</td>
                                    <td style="white-space: nowrap;">{{ $item->kode_transaksi }}</td>
                                    <td style="white-space: nowrap;">{{ $item->customer }}</td>
                                    <td style="white-space: nowrap;">{{ $item->tanggal }}</td>
                                    <td
                                        style="white-space: nowrap; max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                        {{ format_rupiah($item->total_bayar) }}</td>
                                    <td style="white-space: nowrap;">
                                        <span class="badge"
                                            style="background-color: {{ $item->status_pembayaran == 'lunas' ? 'green' : 'red' }}; width: auto; height: 20px; display: inline-block; border-radius: 4px; color: white;">
                                            {{ $item->status_pembayaran }}
                                        </span>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <a class="btn btn-info btn-sm btn-detail" data-toggle="modal" data-target="#detail"
                                            data-id="{{ $item->id }}"><small>Detail</small></a>
                                        <form action="{{ route('kasir.destroy', $item->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirmDelete('{{ $item->kode_transaksi }}')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="btn btn-danger btn-sm"><small>Hapus</small></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row justify-content-end">
            <div class="col-auto m-2">
                <p>Showing {{ $transaksi->firstItem() }} to {{ $transaksi->lastItem() }} of {{ $transaksi->total() }}
                    entries</p>
            </div>
            <div class="col-auto m-2">
                {{ $transaksi->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
    </div>


    @include('modaldetail.detail_kasir')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $('.btn-detail').on('click', function() {
                var id = $(this).data('id');

                console.log('Button clicked, ID:', id); // Debug

                // Set transaksi ID ke button update pembayaran
                $('#btn-update-bayar').data('transaksi-id', id);

                $.ajax({
                    url: '/kasir/' + id + '/detail',
                    type: 'GET',
                    beforeSend: function() {
                        $('#detail-items').html(
                            '<tr><td colspan="6" class="text-center">Loading...</td></tr>');
                    },
                    success: function(response) {
                        console.log('Response received:', response); // Debug

                        // Kosongkan tbody
                        $('#detail-items').empty();

                        if (response.status === 'success' && response.data) {
                            // Loop data detail
                            $.each(response.data, function(index, item) {
                                $('#detail-items').append(`
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${item.nama_barang}</td>
                                        <td>${item.jumlah}</td>
                                        <td>${item.satuan}</td>
                                        <td>Rp ${formatRupiah(item.harga_satuan)}</td>
                                        <td>Rp ${formatRupiah(item.subtotal)}</td>
                                    </tr>
                                `);
                            });

                            // Tampilkan total bayar
                            $('#detail-total').text('Rp ' + formatRupiah(response.transaksi
                                .total_bayar));

                            // Populate informasi transaksi
                            $('#detail-kode').text(response.transaksi.kode_transaksi);
                            $('#detail-customer').text(response.transaksi.customer);
                            $('#detail-tanggal').text(new Date(response.transaksi.tanggal)
                                .toLocaleDateString('id-ID'));
                            $('#detail-status').html('<span class="badge ' + 
                                (response.transaksi.status_pembayaran === 'lunas' ? 'badge-success' : 'badge-danger') + 
                                '">' + response.transaksi.status_pembayaran + '</span>');

                            // Debug: log semua data transaksi
                            console.log('Full transaksi data:', response.transaksi);
                            console.log('Customer Bayar value:', response.transaksi
                                .customer_bayar);

                            // Populate customer bayar input
                            $('#detail-customer-bayar-input').val(response.transaksi
                                .customer_bayar || 0);

                            // Debug: cek apakah input berhasil diisi
                            console.log('Input value after populate:', $(
                                '#detail-customer-bayar-input').val());
                        } else {
                            $('#detail-items').html(
                                '<tr><td colspan="6" class="text-center">Tidak ada data detail</td></tr>'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error); // Debug
                        console.error('Response:', xhr.responseText); // Debug
                        $('#detail-items').html(
                            '<tr><td colspan="6" class="text-center text-danger">Gagal mengambil data detail: ' +
                            error + '</td></tr>');
                    }
                });
            });

            // Fungsi format rupiah
            function formatRupiah(angka) {
                return parseFloat(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            // Handle update pembayaran
            $('#btn-update-bayar').on('click', function() {
                var transaksiId = $(this).data('transaksi-id');
                var customerBayar = $('#detail-customer-bayar-input').val();

                if (!transaksiId) {
                    alert('ID transaksi tidak ditemukan!');
                    return;
                }

                if (!customerBayar || parseFloat(customerBayar) < 0) {
                    alert('Silakan masukkan jumlah bayar yang valid!');
                    return;
                }

                $.ajax({
                    url: '/kasir/edit/' + transaksiId,
                    type: 'PUT',
                    data: {
                        customer_bayar: customerBayar,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $('#btn-update-bayar').prop('disabled', true).text('Updating...');
                    },
                    success: function(response) {
                        alert('Pembayaran berhasil diupdate!');
                        $('#detail').modal('hide');
                        location.reload(); // Reload halaman untuk refresh data
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', xhr.responseText);
                        alert('Gagal update pembayaran: ' + (xhr.responseJSON?.message ||
                            error));
                    },
                    complete: function() {
                        $('#btn-update-bayar').prop('disabled', false).text(
                            'Update Pembayaran');
                    }
                });
            });
        });

        // Fungsi konfirmasi delete
        function confirmDelete(kodeTransaksi) {
            return confirm('Apakah Anda yakin ingin menghapus transaksi ' + kodeTransaksi +
                '?\n\nPerhatian: Transaksi yang dihapus akan diubah statusnya menjadi "dibatalkan" dan tidak dapat dikembalikan.'
            );
        }
    </script>
@endsection
