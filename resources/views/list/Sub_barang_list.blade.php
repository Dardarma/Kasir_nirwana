@extends('layout')
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

                        <form method="GET" action="{{ url('/sub-barang') }}"
                            class="d-flex flex-wrap align-items-center gap-2">
                            <div class="input-group input-group-sm mx-1" style="width: 80px;">
                                <select class="custom-select" name="paginate" onchange="this.form.submit()">
                                    <option value="10" {{ request('paginate') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('paginate') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('paginate') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('paginate') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                            <div class="input-group input-group-sm mx-1" style="width: 80px;">
                                <select class="custom-select" name="costumer" onchange="this.form.submit()">
                                    @foreach ($costumer as $item)
                                        <option value="{{ $item->id }}"
                                            {{ request('costumer') == $item->id ? 'selected' : '' }}>{{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="input-group input-group-sm mx-1" style="width: 150px;">
                                <input type="text" name="search" class="form-control" placeholder="Search"
                                    value="{{ request('search') }}">
                                <div class="input-group-append ">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#add">Tambah
                            Barang</button>
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
                                <th style="min-width: 150px; white-space: nowrap;">Nama Barang</th>
                                <th style="min-width: 80px; white-space: nowrap;">Nama Sub</th>
                                <th style="min-width: 80px; white-space: nowrap;">Costumer</th>
                                <th style="min-width: 80px; white-space: nowrap;">Harga</th>
                                <th style="min-width: 80px; white-space: nowrap;">Satuan</th>
                                <th style="min-width: 120px; white-space: nowrap;">Gramasi</th>
                                <th style="min-width: 120px; white-space: nowrap;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>

                            @if (count($produk) == 0)
                                <tr>
                                    <td colspan="7" class="text-center">Data not found</td>
                                </tr>
                            @endif
                            @foreach ($produk as $key => $item)
                                <tr>
                                    <td style="white-space: nowrap;">{{ $key + 1 }}</td>
                                    <td style="white-space: nowrap;">{{ $item->barang->nama_barang }}</td>
                                    <td style="white-space: nowrap;">{{ $item->sub_nama }}</td>
                                    <td style="white-space: nowrap;">{{ $item->user->name }}</td>
                                    <td style="white-space: nowrap;">{{ format_rupiah($item->harga) }} </td>
                                    <td style="white-space: nowrap;">{{ $item->satuan }}</td>
                                    <td
                                        style="white-space: nowrap; max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $item->gramasi }} gr</td>
                                    <td style="white-space: nowrap;">
                                        <a class="btn btn-warning btn-sm btn-edit" data-toggle="modal" data-target="#edit"
                                            data-id = "{{ $item->id }}" data-barang-id = "{{ $item->barang_id }}"
                                            data-nama-barang = "{{ $item->barang->nama_barang }}"
                                            data-sub-nama = "{{ $item->sub_nama }}"
                                            data-costumer-id = "{{ $item->user_id }}"
                                            data-harga = "{{ $item->harga }}" data-satuan = "{{ $item->satuan }}"
                                            data-gramasi = "{{ $item->gramasi }}"><small>Edit</small></a>
                                        <a class="btn btn-danger btn-sm btn-delete" data-toggle="modal"
                                            data-target="#delete" data-id="{{ $item->id }}"
                                            data-nama_barang="{{ $item->sub_nama }}"><small>Hapus</small></a>
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
                <p>Showing {{ $produk->firstItem() }} to {{ $produk->lastItem() }} of {{ $produk->total() }} entries</p>
            </div>
            <div class="col-auto m-2">
                {{ $produk->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
    </div>
    @include('modal.sub_barang_add_modal')
    @include('modal.sub_barang_edit_modal')

    {{-- Modal Delete Confirmation --}}
    <div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
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
        $(document).ready(function() {
            // ===== ADD MODAL SCRIPT =====
            // Event handler untuk perubahan satuan
            $('#add_satuan_select').on('change', function() {
                var satuan = $(this).val();
                var gramasiInput = $('#add_gramasi');
                
                if (satuan === 'gram') {
                    // Jika satuan gram, set gramasi ke 1 dan readonly
                    gramasiInput.val(1);
                    gramasiInput.prop('readonly', true);
                } else if (satuan === 'kg') {
                    // Jika satuan kg, set gramasi ke 1000 dan readonly
                    gramasiInput.val(1000);
                    gramasiInput.prop('readonly', true);
                } else if (satuan === 'pcs') {
                    // Jika satuan pcs, enable input dan kosongkan nilai
                    gramasiInput.val('');
                    gramasiInput.prop('readonly', false);
                } else {
                    // Default: enable input dan kosongkan nilai
                    gramasiInput.val('');
                    gramasiInput.prop('readonly', false);
                }
            });
            
            // Reset form saat modal add dibuka
            $('#add').on('show.bs.modal', function () {
                $('#add_gramasi').val('');
                $('#add_gramasi').prop('readonly', false);
            });

            // ===== EDIT MODAL SCRIPT =====
            // Event handler untuk perubahan satuan di modal edit
            $('#satuan').on('change', function() {
                var satuan = $(this).val();
                var gramasiInput = $('#gramasi');
                
                if (satuan === 'gram') {
                    // Jika satuan gram, set gramasi ke 1 dan readonly
                    gramasiInput.val(1);
                    gramasiInput.prop('readonly', true);
                } else if (satuan === 'kg') {
                    // Jika satuan kg, set gramasi ke 1000 dan readonly
                    gramasiInput.val(1000);
                    gramasiInput.prop('readonly', true);
                } else if (satuan === 'pcs') {
                    // Jika satuan pcs, enable input
                    gramasiInput.prop('readonly', false);
                    // Jangan ubah nilai saat edit, biarkan nilai existing
                } else {
                    // Default: enable input
                    gramasiInput.prop('readonly', false);
                }
            });

            // Ketika modal edit dibuka, check satuan yang sudah terpilih
            $('#edit').on('shown.bs.modal', function () {
                // Trigger change event untuk set readonly berdasarkan satuan yang sudah dipilih
                var currentSatuan = $('#satuan').val();
                if (currentSatuan) {
                    $('#satuan').trigger('change');
                }
            });

            // ===== EDIT BUTTON CLICK HANDLER =====
            $('.btn-edit').on('click', function() {
                var id = $(this).data('id');
                var barang_id = $(this).data('barang-id');
                var sub_nama = $(this).data('sub-nama');
                var costumer_id = $(this).data('costumer-id');
                var satuan = $(this).data('satuan');
                var gramasi = $(this).data('gramasi');
                var harga = $(this).data('harga');

                console.log('Data loaded:', {
                    id: id,
                    barang_id: barang_id,
                    sub_nama: sub_nama,
                    costumer_id: costumer_id,
                    satuan: satuan,
                    gramasi: gramasi,
                    harga: harga
                });

                // Set values to existing form fields dengan ID asli
                $('#nama_barang').val(barang_id);
                $('#sub_nama').val(sub_nama); // Nama sub barang
                $('#costumer').val(costumer_id);
                $('#satuan').val(satuan); // Set satuan
                $('#gramasi').val(gramasi);
                $('#harga').val(harga);

                // Trigger change event pada satuan untuk handle readonly gramasi
                $('#satuan').trigger('change');

                // Set the form action to the correct URL
                $('#edit form').attr('action', '/sub-barang/' + id);
            });

            // ===== DELETE BUTTON HANDLER =====
            $('.btn-delete').on('click', function() {
                const id = $(this).attr('data-id');
                const nama_barang = $(this).attr('data-nama_barang');

                // Set the form action to the correct URL using the route
                $('#delete-form').attr('action', '/sub-barang/delete/' + id);

                // Set the confirmation message
                $('#delete-message').html(
                    `Apakah Anda yakin ingin menghapus sub barang <strong>${nama_barang}</strong>?`
                );
            });
        });
    </script>
@endsection
