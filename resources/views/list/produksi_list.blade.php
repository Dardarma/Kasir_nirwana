@extends('layout')
@section('content')
    <!-- Alert Messages -->
    @if(session('success'))
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

    @if(session('error'))
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
                        Data Transaksi Produksi
                    </h3>
                </div>
                <div class="col-md-8">
                    <div class="d-flex justify-content-end align-items-center flex-wrap gap-2">
                        <!-- Form Search & Pagination -->
                        <form method="GET" action="{{ url('/produksi/list') }}" class="d-flex align-items-center gap-2">
                            <!-- Show entries -->
                            <div class="input-group input-group-sm" style="width: 80px;">
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
                        <a href="{{ url('/produksi') }}" class="btn btn-primary btn-sm">
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
                                <th style="min-width: 150px; white-space: nowrap;">Tanggal</th>
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
                                    <td style="white-space: nowrap;">{{ $item->tanggal }}</td>
                                    <td style="white-space: nowrap;">
                                        <a class="btn btn-info btn-sm btn-detail" data-toggle="modal" data-target="#detail"
                                            data-id="{{ $item->id }}"><small>Detail</small></a>
                                        <form action="{{ route('produksi.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirmDelete('{{ $item->kode_transaksi }}')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-danger btn-sm"><small>Hapus</small></button>
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
                {{ $transaksi->links() }}
            </div>
        </div>
    </div>
    </div>

    
    @include('modaldetail.detail_produksi')
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
                    url: '/produksi/' + id + '/detail',
                    type: 'GET',
                    beforeSend: function() {
                        $('#detail-items').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
                    },
                    success: function(response) {
                        console.log('Response received:', response); // Debug
                        
                        // Kosongkan tbody
                        $('#detail-items').empty();

                        if (response.status === 'success' && response.data && response.data.length > 0) {
                            // Loop data detail
                            $.each(response.data, function(index, item) {
                                console.log('Processing item:', item); // Debug setiap item
                                
                                $('#detail-items').append(`
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${item.nama_barang || 'N/A'}</td>
                                        <td>${item.jumlah || 0}</td>
                                        <td>${item.satuan || 'N/A'}</td>
                                    </tr>
                                `);
                            });

                            // Populate informasi transaksi
                            if (response.transaksi) {
                                $('#detail-kode').text(response.transaksi.kode_transaksi || 'N/A');
                                $('#detail-tanggal').text(response.transaksi.tanggal ? new Date(response.transaksi.tanggal).toLocaleDateString('id-ID') : 'N/A');
                                
                                console.log('Transaksi data populated');
                            }
                        } else {
                            console.log('No data found or invalid response structure');
                            $('#detail-items').html('<tr><td colspan="4" class="text-center">Tidak ada data detail</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error); // Debug
                        console.error('Status:', status); // Debug
                        console.error('Response:', xhr.responseText); // Debug
                        console.error('Status Code:', xhr.status); // Debug
                        $('#detail-items').html('<tr><td colspan="4" class="text-center text-danger">Gagal mengambil data detail: ' + error + '</td></tr>');
                    }
                });
            });

            // Fungsi format rupiah
            function formatRupiah(angka) {
                return parseFloat(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

    
        });

        // Fungsi konfirmasi delete
        function confirmDelete(kodeTransaksi) {
            return confirm('Apakah Anda yakin ingin menghapus transaksi ' + kodeTransaksi + '?\n\nPerhatian: Transaksi yang dihapus akan diubah statusnya menjadi "dibatalkan" dan tidak dapat dikembalikan.');
        }
    </script>
@endsection
