<div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="addModuleLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Edit Sub Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="nama_barang">Nama Barang</label>
                    <select class="form-control" name="nama_barang" id="nama_barang">
                        <option value="">-- Pilih Barang --</option>
                        @foreach ($barang as $item)
                            <option value="{{ $item->id }}">{{ $item->nama_barang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="nama">Nama Sub Barang</label>
                    <input type="text" class="form-control" name="sub_nama" id="sub_nama" required>
                </div>
                <div class="form-group">
                    <label for="nama">Costumer</label>
                    <select class="form-control" name="costumer" id="costumer" required>
                        <option value="">-- Pilih Costumer --</option>
                        @foreach ($costumer as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="nama">Satuan</label>
                    <select class="form-control" name="satuan" id="satuan" required>
                        <option value="">-- Pilih Satuan --</option>
                        <option value="kg">KG</option>
                        <option value="gram">Gram</option>
                        <option value="pcs">PCS</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nama">Gramasi</label>
                    <input type="number" class="form-control" name="gramasi" id="gramasi" required>
                </div>
                <div class="form-group">
                    <label for="nama">Harga</label>
                    <input type="number" class="form-control" name="harga" id="harga" required>
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
