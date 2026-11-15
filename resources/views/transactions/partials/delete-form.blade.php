@php($phrase = \App\Http\Requests\ConfirmTransactionDeletion::PHRASE)

<div class="modal fade" id="transaction-delete-modal" tabindex="-1" aria-labelledby="transaction-delete-title" aria-hidden="true">
    <div class="modal-dialog">
        <form id="transaction-delete-form" method="POST" class="modal-content" data-phrase="{{ $phrase }}">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="transaction-delete-title">Hapus Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Transaksi <strong id="transaction-delete-code"></strong> akan dibatalkan.</p>
                <label for="konfirmasi_hapus">Ketik <strong>"{{ $phrase }}"</strong></label>
                <input id="konfirmasi_hapus" name="konfirmasi_hapus" type="text" class="form-control"
                    autocomplete="off" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger" disabled>Hapus Transaksi</button>
            </div>
        </form>
    </div>
</div>
