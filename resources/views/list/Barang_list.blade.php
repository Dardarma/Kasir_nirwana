@extends('layout')
@section('content-header')
<h2>
    Input Barang
</h2>
@endsection
@section('content')

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <div class="d-flex justify-content-between align-items-center">
                <span>{{ session('success') }}</span>
                <small class="text-muted ml-2" id="success-time"></small>
            </div>
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
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
            <div class="d-flex justify-content-between align-items-center">
                <span>{{ session('error') }}</span>
                <small class="text-muted ml-2" id="error-time"></small>
            </div>
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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="validation-alert">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <small class="text-muted ml-2" id="validation-time"></small>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <script>
           
            // Auto hide after 2 seconds
            setTimeout(function() {
                var alert = document.getElementById('validation-alert');
                if (alert) {
                    alert.classList.remove('show');
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 150); // Wait for fade transition
                }
            }, 2000);
        </script>
    @endif

 <div class="card mt-4">
                <div class="card-header">
                    <div class="row align-items-center">

                        <div class="col-12 col-md">
                            <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-2">
                                
                                <form method="GET" action="{{ url('/barang') }}" class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="input-group input-group-sm mx-1" style="width: 80px;">
                                        <select class="custom-select" name="paginate" onchange="this.form.submit()">
                                            <option value="10" {{ request('paginate') == 10 ? 'selected' : '' }}>10</option>
                                            <option value="25" {{ request('paginate') == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('paginate') == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('paginate') == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </div>
                                     <div class="input-group input-group-sm mx-1" style="width: 80px;">
                                        <select class="custom-select" name="Kategori" onchange="this.form.submit()">
                                            <option value="semua" {{ request('Kategori') == 'semua' ? 'selected' : '' }}>Semua</option>
                                            <option value="produksi" {{ request('Kategori') == 'produksi' ? 'selected' : '' }}>Produksi</option>
                                            <option value="jadi" {{ request('Kategori') == 'jadi' ? 'selected' : '' }}>Jadi</option>
                                        </select>
                                    </div>

                                    <div class="input-group input-group-sm mx-1" style="width: 150px;">
                                        <input type="text" name="search" class="form-control" placeholder="Search" value="{{ request('search') }}">
                                        <div class="input-group-append ">
                                            <button type="submit" class="btn btn-default">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                        
                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#add">Tambah Barang</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                

                <div class="card-body">  
                    <div class="table-responsive">
                        <div class="table-wrapper" style="overflow-x: auto; border-radius: 10px;">
                            <table id="data" class="table table-bordered table-hover" style="border-radius: 10px; min-width: 900px;">
                              <thead style="background-color: #578FCA; color: white;">
                                <tr>
                                    <th style="min-width: 60px; white-space: nowrap;">No</th>
                                    <th style="min-width: 150px; white-space: nowrap;">Nama Barang</th>
                                    <th style="min-width: 80px; white-space: nowrap;">Satuan</th>
                                    <th style="min-width: 80px; white-space: nowrap;">Kategori</th>
                                    <th style="min-width: 80px; white-space: nowrap;">Sub Kategori</th>
                                    <th style="min-width: 120px; white-space: nowrap;">Harga</th>
                                    <th style="min-width: 120px; white-space: nowrap;">Aksi</th>
                                </tr>
                            </thead>
                        <tbody>

                            @if(count($barang) == 0)
                                <tr>
                                    <td colspan="7" class="text-center">Data not found</td>
                                </tr>
                            @endif
                            @foreach ($barang as $key => $item)
                                <tr>
                                    <td style="white-space: nowrap;">{{ $barang->firstItem() + $key }}</td>
                                    <td style="white-space: nowrap;">{{ $item->nama_barang }}</td>
                                    <td style="white-space: nowrap;">{{ $item->satuan }}</td>
                                    <td style="white-space: nowrap;" >{{ $item->kategori }}</td>
                                    <td style="white-space: nowrap;">{{ $item->sub_kategori }}</td>
                                    <td style="white-space: nowrap; max-width: 300px; overflow: hidden; text-overflow: ellipsis;" >Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td style="white-space: nowrap;">
                                        <a
                                         class="btn btn-warning btn-sm btn-edit"  
                                         data-toggle="modal" 
                                         data-target="#edit"
                                         data-id = "{{ $item->id }}"
                                         data-nama_barang  ="{{ $item->nama_barang }}"
                                         data-satuan = "{{ $item->satuan }}"
                                         data-kategori = "{{ $item->kategori }}"
                                         data-sub_kategori = "{{ $item->sub_kategori }}"
                                         data-harga = "{{ $item->harga }}"
                                         ><small>Edit</small></a>
                                        <a class="btn btn-danger btn-sm btn-delete"
                                         data-toggle="modal"
                                         data-target="#delete"
                                         data-id="{{ $item->id }}"
                                         data-nama_barang="{{ $item->nama_barang }}"
                                        ><small>Hapus</small></a>
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
                        <p>Showing {{ $barang->firstItem() }} to {{ $barang->lastItem() }} of {{ $barang->total() }} entries</p>
                    </div>
                    <div class="col-auto m-2">
                        {{$barang->links()}}
                    </div>
                </div>
                </div>
            </div>
@include('barang_add_modal')
@include('barang_edit_modal')

{{-- Modal Delete Confirmation --}}
<div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="delete-form">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p id="delete-message">Apakah Anda yakin ingin menghapus barang ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
    $('.btn-edit').on('click', function() {
        var id = $(this).data('id');
        var nama_barang = $(this).data('nama_barang');
        var satuan = $(this).data('satuan');
        var kategori = $(this).data('kategori');
        var sub_kategori = $(this).data('sub_kategori');
        var harga = $(this).data('harga');

        $('#edit').find('input[name="nama"]').val(nama_barang);
        $('#edit').find('input[name="satuan"]').val(satuan);
        $('#edit').find('#kategori_edit').val(kategori);
        $('#edit').find('#sub_kategori_edit').val(sub_kategori);
        $('#edit').find('input[name="harga"]').val(harga);
        
        // Set the form action to the correct URL
        $('#edit form').attr('action', '/barang/' + id);
    });

    document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.btn-edit');
    
    editButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const data = {
                id: this.getAttribute('data-id'),
                nama_barang: this.getAttribute('data-nama_barang'),
                satuan: this.getAttribute('data-satuan'),
                kategori: this.getAttribute('data-kategori'),
                sub_kategori: this.getAttribute('data-sub_kategori'),
                harga: this.getAttribute('data-harga')
            };
            
            // Panggil fungsi untuk populate form edit
            if (typeof populateEditForm === 'function') {
                populateEditForm(data);
            }
        });
    });
});

document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const nama_barang = this.getAttribute('data-nama_barang');
        
        // Set the form action to the correct URL using the route
        document.querySelector('#delete-form').setAttribute('action', '/barang/delete/' + id);
        
        // Set the confirmation message
        document.querySelector('#delete-message').innerHTML = `Apakah Anda yakin ingin menghapus barang <strong>${nama_barang}</strong>?`;
    });
});

</script>
@endsection
