<div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="addModuleLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Add Sub Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('sub_barang.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="nama_barang">Nama Barang</label>
                        <select class="form-control" name="barang" id="add_nama_barang">
                            <option value="">-- Pilih Barang --</option>
                            @foreach($barang as $item)
                                <option value="{{ $item->id }}" data-satuan="{{ $item->satuan }}" data-non-stok="{{ (int) $item->isNonStok() }}">{{ $item->nama_barang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama Sub Barang</label>
                        <input type="text" class="form-control" name="sub_nama" id="add_sub_nama" required>
                    </div>
                    <div class="form-group">
                        <label for="add_customer">Customer</label>
                        <select class="form-control" name="customer" id="add_customer" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nama">Satuan</label>
                        <select class="form-control" name="satuan" id="add_satuan_select" required>
                            <option value="">-- Pilih Satuan --</option>
                        </select>
                    </div>
                     <div class="form-group">
                        <label>Konversi ke satuan dasar</label>
                        <input type="text" class="form-control" data-konversi readonly>
                    </div>
                    <div class="form-group">
                        <label for="add_harga">Harga</label>
                        <input type="number" class="form-control" name="harga" id="add_harga" required>
                    </div>
                    <div class="my-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
