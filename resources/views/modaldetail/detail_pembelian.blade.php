<div class="modal fade" id="detail" tabindex="-1" role="dialog" aria-labelledby="addModuleLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Detail Transaksi Pembelian</h5>
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
                            <tr>
                                <td><strong>Customer Bayar:</strong></td>
                                <td>
                                    <input type="number" class="form-control" name="customer_bayar" id="detail-customer-bayar-input" min="0" step="0.01">
                                </td>
                            </tr>
                             <tr>
                                <td><strong>Tanggal Pembayaran:</strong></td>
                                <td id="detail-tanggal-pembayaran"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Tanggal:</strong></td>
                                <td id="detail-tanggal"></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td id="detail-status"></td>
                            </tr>
                             <tr>
                                <td><strong>Metode Pembayaran:</strong></td>
                                <td id="detail-metode-pembayaran"></td>
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
                                <th style="width: 35%;">Nama Barang</th>
                                <th style="width: 15%;">Jumlah</th>
                                <th style="width: 10%;">Satuan</th>
                                <th style="width: 20%;">Harga</th>
                                <th style="width: 20%;">Sub Total</th>
                            </tr>
                        </thead>
                        <tbody id="detail-items">

                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                <td colspan="5" class="text-right">Total Bayar:</td>
                                <td id="detail-total"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" id="btn-update-bayar">Update Pembayaran</button>
            </div>
        </div>
    </div>
</div>
