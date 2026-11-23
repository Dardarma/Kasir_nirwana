@php
    $tipeTransaksi = ['kasir' => 'penjualan', 'pembelian' => 'pembelian', 'pemakaian' => 'pemakaian', 'produksi' => 'produksi'][$jenis];
    $barangLaporan = $jenis === 'kasir'
        ? \App\Models\sub_barang::query()
            ->join('transaksi_detail', 'transaksi_detail.sub_barang_id', '=', 'sub_barang.id')
            ->join('transaksi', 'transaksi.id', '=', 'transaksi_detail.transaksi_id')
            ->where('transaksi.jenis_transaksi', $tipeTransaksi)
            ->where('transaksi.status', 'aktif')
            ->distinct()->orderBy('sub_barang.sub_nama')
            ->get(['sub_barang.id', 'sub_barang.sub_nama', 'transaksi.customer_id'])
        : \App\Models\Barang::query()
            ->join('transaksi_detail', 'transaksi_detail.barang_id', '=', 'barang.id')
            ->join('transaksi', 'transaksi.id', '=', 'transaksi_detail.transaksi_id')
            ->where('transaksi.jenis_transaksi', $tipeTransaksi)
            ->where('transaksi.status', 'aktif')
            ->distinct()->orderBy('barang.nama_barang')
            ->get(['barang.id', 'barang.nama_barang']);
    $pelangganLaporan = $jenis === 'kasir'
        ? \App\Models\Customer::query()
            ->join('transaksi', 'transaksi.customer_id', '=', 'customers.id')
            ->where('transaksi.jenis_transaksi', 'penjualan')
            ->where('transaksi.status', 'aktif')
            ->distinct()->orderBy('customers.name')->get(['customers.id', 'customers.name'])
        : collect();
@endphp

<div class="modal fade" id="modal-laporan-pdf" tabindex="-1" role="dialog" aria-labelledby="judul-laporan-pdf" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="GET" action="{{ route('reports.pdf', [], false) }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="judul-laporan-pdf">Cetak PDF Laporan {{ ucfirst($jenis) }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="jenis" value="{{ $jenis }}">
                    <input type="hidden" name="sumber" value="transaksi">
                    <div class="form-group">
                        <label for="pdf-tanggal-dari">Tanggal dari</label>
                        <input id="pdf-tanggal-dari" name="tanggal_dari" type="date" class="form-control" value="{{ request('tanggal_dari') }}">
                    </div>
                    <div class="form-group">
                        <label for="pdf-tanggal-sampai">Tanggal sampai</label>
                        <input id="pdf-tanggal-sampai" name="tanggal_sampai" type="date" class="form-control" value="{{ request('tanggal_sampai') }}">
                    </div>
                    <div class="form-group">
                        <label for="pdf-barang">Barang</label>
                        <select id="pdf-barang" name="{{ $jenis === 'kasir' ? 'sub_barang_id' : 'barang_id' }}" class="form-control">
                            <option value="">Semua barang</option>
                            @foreach ($barangLaporan as $barang)
                                <option value="{{ $barang->id }}" @if ($jenis === 'kasir') data-customer-id="{{ $barang->customer_id }}" @endif>{{ $jenis === 'kasir' ? $barang->sub_nama : $barang->nama_barang }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($jenis === 'kasir')
                        <div class="form-group">
                            <label for="pdf-pelanggan">Pelanggan</label>
                            <select id="pdf-pelanggan" name="customer_id" class="form-control">
                                <option value="">Semua pelanggan</option>
                                @foreach ($pelangganLaporan as $pelanggan)
                                    <option value="{{ $pelanggan->id }}">{{ $pelanggan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <small class="text-muted">Kosongkan filter untuk mencetak seluruh transaksi {{ $jenis }}.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Unduh PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>
@if ($jenis === 'kasir')
    <script>
        document.getElementById('pdf-pelanggan').addEventListener('change', function () {
            const customerId = this.value;
            const barangSelect = document.getElementById('pdf-barang');
            for (const option of barangSelect.options) {
                if (!option.value) continue;
                option.hidden = Boolean(customerId && option.dataset.customerId !== customerId);
                option.disabled = option.hidden;
            }
            if (barangSelect.selectedOptions[0]?.hidden) barangSelect.value = '';
        });
    </script>
@endif
