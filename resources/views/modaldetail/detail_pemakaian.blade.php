<div class="modal fade" id="detail" tabindex="-1" role="dialog" aria-labelledby="addModuleLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Detail Transaksi Pemakaian</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Informasi Transaksi -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Kode Transaksi:</strong></td>
                                <td id="detail-kode"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Tanggal:</strong></td>
                                <td id="detail-tanggal"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Table Detail Items -->
                <div class="table-responsive mt-0">
                    <table class="table table-bordered table-hover">
                        <thead style="background-color: #578FCA; color: white;">
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 50%;">Nama Barang</th>
                                <th style="width: 25%;">Jumlah</th>
                                <th style="width: 20%;">Satuan</th>
                            </tr>
                        </thead>
                        <tbody id="detail-items">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
