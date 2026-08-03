@extends('layout')
@section('content-header')
@endsection
@section('content')

    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-6">
                    <h2>
                        Pelanggan
                    </h2>
                </div>

                <div class="col-6 col-md">
                    <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-2">

                        <form method="GET" action="{{ url('/barang') }}"
                            class="d-flex flex-wrap align-items-center gap-2">
                            <div class="input-group input-group-sm mx-1" style="width: 80px;">
                                <select class="custom-select" name="paginate" onchange="this.form.submit()">
                                    <option value="10" {{ request('paginate') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('paginate') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('paginate') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('paginate') == 100 ? 'selected' : '' }}>100</option>
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

                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#add">Tambah Pelanggan</button>
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
                                <th style="min-width: 150px; white-space: nowrap;">Nama Pelangganz</th>
                                <th style="min-width: 120px; white-space: nowrap;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>

                            @if (count($costumers) == 0)
                                <tr>
                                    <td colspan="7" class="text-center">Data not found</td>
                                </tr>
                            @endif
                            @foreach ($costumers as $key => $item)
                                <tr>
                                    <td style="white-space: nowrap;">{{ $costumers->firstItem() + $key }}</td>
                                    <td style="white-space: nowrap;">{{ $item->name }}</td>
                                    <td style="white-space: nowrap;">
                                        <a class="btn btn-warning btn-sm btn-edit" data-toggle="modal" data-target="#edit"
                                            data-id = "{{ $item->id }}"
                                            data-name = "{{ $item->name }}"><small>Edit</small></a>
                                        <a class="btn btn-danger btn-sm btn-delete" data-toggle="modal"
                                            data-target="#delete" data-id="{{ $item->id }}"
                                            data-name="{{ $item->name }}"><small>Hapus</small></a>
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
                <p>Showing {{ $costumers->firstItem() }} to {{ $costumers->lastItem() }} of {{ $costumers->total() }}
                    entries</p>
            </div>
            <div class="col-auto m-2">
                {{ $costumers->links() }}
            </div>
        </div>
    </div>
    </div>
    @include('modal.costumer_add_modal')
    @include('modal.costumer_edit_modal')

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
                        <p id="delete-message">Apakah Anda yakin ingin menghapus costumer ini?</p>
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
            var name = $(this).data('name');

            $('#edit').find('input[name="name"]').val(name);

            // Set the form action to the correct URL
            $('#edit form').attr('action', '/costumer/' + id);
        });



        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                // Set the form action to the correct URL using the route
                document.querySelector('#delete-form').setAttribute('action', '/costumer/delete/' + id);

                // Set the confirmation message
                document.querySelector('#delete-message').innerHTML =
                    `Apakah Anda yakin ingin menghapus costumer <strong>${name}</strong>?`;
            });
        });
    </script>
@endsection
