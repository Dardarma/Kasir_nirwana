@extends('layouts.app')
@section('content-header')
<h2>
    Input Barang
</h2>
@endsection
@section('content')

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
                                    <th style="min-width: 150px; white-space: nowrap;">Nama barang</th>
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
                                    <td style="white-space: nowrap;">{{ $item->sub_kategori === 'non_stok' ? 'Nonstok' : $item->sub_kategori }}</td>
                                    <td style="white-space: nowrap; max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                        {{ (float) $item->harga == 0 ? '-' : 'Rp ' . number_format($item->harga, 0, ',', '.') }}
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <a
                                         class="btn btn-warning btn-sm btn-edit"  
                                         data-toggle="modal" 
                                         data-target="#edit"
                                         data-id = "{{ $item->id }}"
                                         data-update-url="{{ route('Barang_update', $item->id) }}"
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
@include('barang.partials.create-modal')
@include('barang.partials.edit-modal')

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
    const addBarangForm = document.querySelector('#add form');
    const editBarangForm = document.getElementById('edit-form');

    function updateBarangFields(form) {
        const kategori = form.elements.kategori.value;
        const subKategori = form.elements.sub_kategori;
        const group = form.querySelector('[data-sub-kategori]');
        group.hidden = group.disabled = !kategori;
        Array.from(subKategori.options).forEach(option => {
            option.hidden = option.disabled = !!option.dataset.optionKategori && option.dataset.optionKategori !== kategori;
        });
        if (subKategori.selectedOptions[0]?.disabled || !subKategori.value) {
            subKategori.value = kategori === 'jadi' ? 'barang_jadi' : '';
        }


    }

    [addBarangForm, editBarangForm].forEach(form => {
        form.elements.kategori.addEventListener('change', () => updateBarangFields(form));
        form.elements.sub_kategori.addEventListener('change', () => updateBarangFields(form));
        updateBarangFields(form);
    });

    addBarangForm.addEventListener('submit', () => {
        const submitButton = addBarangForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Menyimpan...';
    });

    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', () => {
            editBarangForm.reset();
            const data = button.dataset;
            editBarangForm.action = button.dataset.updateUrl;
            ['id', 'nama_barang', 'kategori', 'satuan', 'sub_kategori'].forEach(name => {
                editBarangForm.elements.namedItem(name).value = data[name];
            });
            updateBarangFields(editBarangForm);
        });
    });

document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const nama_barang = this.getAttribute('data-nama_barang');
        
        // Set the form action to the correct URL using the route
        document.querySelector('#delete-form').setAttribute('action', '/barang/delete/' + id);
        
        // Set the confirmation message
        document.querySelector('#delete-message').innerHTML = `Apakah Anda yakin ingin menghapus barang <strong>${escapeHtmlText(nama_barang)}</strong>?`;
    });
});

</script>
@endsection
