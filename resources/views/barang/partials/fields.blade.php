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
                    <fieldset data-kategori="jadi" hidden disabled>
                        @if (auth()->user()->hasPermission('sub-barang.create'))
                            <div class="form-group">
                                <label for="{{ $modal }}_sub_barang">Sub Barang Baru (opsional)</label>
                                <input type="text" class="form-control" id="{{ $modal }}_sub_barang" name="sub_nama" maxlength="255">
                            </div>
                            <div class="form-group" data-sub-barang-customer hidden>
                                <label for="{{ $modal }}_customer">Pelanggan Sub Barang</label>
                                <select class="form-control" id="{{ $modal }}_customer" name="customer_id" disabled>
                                    <option value="">-- Pilih Pelanggan --</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="form-group" data-harga-barang>
                            <label for="{{ $modal }}_harga">Harga</label>
                            <input type="number" class="form-control" id="{{ $modal }}_harga" name="harga" min="0" required>
                        </div>
                    </fieldset>
