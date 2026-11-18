                    <div class="form-group">
                        <label for="{{ $modal }}_nama_barang">Nama Barang</label>
                        <input type="text" class="form-control" id="{{ $modal }}_nama_barang" name="nama_barang" required>
                    </div>
                    <div class="form-group">
                        <label for="{{ $modal }}_kategori">Kategori</label>
                        <select class="form-control" id="{{ $modal }}_kategori" name="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="produksi">Produksi</option>
                            <option value="jadi">Jadi</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="{{ $modal }}_satuan">Satuan</label>
                        <select class="form-control" id="{{ $modal }}_satuan" name="satuan" required>
                            <option value="">-- Pilih Satuan --</option>
                            @foreach ($satuanList as $unit)
                                <option value="{{ $unit }}">{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <fieldset data-sub-kategori hidden disabled>
                        <div class="form-group">
                            <label for="{{ $modal }}_sub_kategori">Sub Kategori</label>
                            <select class="form-control" id="{{ $modal }}_sub_kategori" name="sub_kategori" required>
                                <option value="">-- Pilih Sub Kategori --</option>
                                <option value="barang_jadi" data-option-kategori="jadi">Barang Jadi (Stok)</option>
                                <option value="non_stok" data-option-kategori="jadi">Nonstok</option>
                                <option data-option-kategori="produksi" value="bahan_baku">Bahan Baku</option>
                                <option data-option-kategori="produksi" value="penolong/alat">Penolong/Alat</option>
                                <option data-option-kategori="produksi" value="produk_jadi">Produk Jadi</option>
                            </select>
                        </div>
                    </fieldset>
