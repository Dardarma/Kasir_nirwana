@extends('layouts.app')

@section('content')
    @php
        $user = auth()->user();
        $canCreate = $user->hasPermission('barang.create');
        $canEdit = $user->hasPermission('barang.update');
        $canDelete = $user->hasPermission('barang.delete');
    @endphp

    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title">Master Satuan</h3>
            @if ($canCreate)
                <div class="ml-auto">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create-satuan">
                        <i class="fas fa-plus mr-1" aria-hidden="true"></i> Tambah Satuan
                    </button>
                </div>
            @endif
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-bordered mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Satuan</th>
                        <th>Satuan terkecil</th>
                        <th>Bergantung pada</th>
                        <th>Faktor</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($units as $unit)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $unit->nama }}</td>
                            <td>{{ $unit->is_satuan_terkecil ? 'Ya' : 'Tidak' }}</td>
                            <td>{{ $unit->parent?->nama }}</td>
                            <td>{{ $unit->faktor === null ? '' : rtrim(rtrim(number_format($unit->faktor, 6, '.', ''), '0'), '.') }}</td>
                            <td>
                                @if ($canEdit)
                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                        data-target="#edit-satuan-{{ $unit->id }}">
                                        <i class="fas fa-edit" aria-hidden="true"></i> Edit
                                    </button>
                                @endif
                                @if ($canDelete)
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                        data-target="#delete-satuan-{{ $unit->id }}">
                                        <i class="fas fa-trash" aria-hidden="true"></i> Hapus
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data satuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($canCreate)
        @include('satuan.form-modal', ['unit' => null])
    @endif

    @foreach ($units as $unit)
        @if ($canEdit)
            @include('satuan.form-modal')
        @endif
        @if ($canDelete)
            <div class="modal fade" id="delete-satuan-{{ $unit->id }}" tabindex="-1"
                aria-labelledby="delete-satuan-title-{{ $unit->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('satuan.destroy', $unit) }}" method="POST" class="modal-content">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header">
                            <h5 class="modal-title" id="delete-satuan-title-{{ $unit->id }}">Hapus Satuan</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">Hapus satuan <strong>{{ $unit->nama }}</strong>?</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
@endsection

@section('script')
    <script>
        document.querySelectorAll('input[name="is_satuan_terkecil"]').forEach((checkbox) => {
            const conversionFields = checkbox.form.elements.namedItem('konversi');
            const updateConversionFields = () => {
                conversionFields.disabled = checkbox.checked;
            };

            checkbox.addEventListener('change', updateConversionFields);
            updateConversionFields();
        });
    </script>
@endsection
