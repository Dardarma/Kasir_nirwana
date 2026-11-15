@php($modalId = $unit ? 'edit-satuan-' . $unit->id : 'create-satuan')

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-title" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ $unit ? route('satuan.update', $unit) : route('satuan.store') }}" method="POST" class="modal-content">
            @csrf
            @if ($unit)
                @method('PUT')
            @endif
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}-title">{{ $unit ? 'Edit Satuan' : 'Tambah Satuan' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                @include('satuan.fields')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
