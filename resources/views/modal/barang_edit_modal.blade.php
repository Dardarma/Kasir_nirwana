<div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="addModuleLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Edit Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="" id="edit-form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-id" name="id">
                    <div class="form-group">
                        <label for="nama_barang_edit">Nama Barang</label>
                        <input type="text" id="nama" class="form-control" name="nama_barang" required>
                    </div>
                    <div class="form-group">
                        <label for="nama">Satuan</label>
                        <input type="text" id="satuan" class="form-control" name="satuan" required>
                    </div>
                    <div class="form-group">
                        <div class="form-group">
                            <label for="nama">Kategori</label>
                            <select class="form-control" name="kategori" id="kategori_edit"
                                onchange="changeKategoriEdit()" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="produksi">Produksi</option>
                                <option value="jadi">Jadi</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nama">Sub Kategori</label>
                        <select class="form-control" name="sub_kategori" id="sub_kategori_edit" required>
                            <option value="">-- Pilih Sub Kategori --</option>
                        </select>
                    </div>
                    <script>
                        // Function untuk populate data ke form edit
                        function populateEditForm(data) {
                            // Set form action
                            document.getElementById('edit-form').action = '/barang/' + data.id;
                            document.getElementById('edit-id').value = data.id;

                            // Populate form fields
                            document.getElementById('nama').value = data.nama_barang;
                            document.getElementById('satuan').value = data.satuan;
                            document.getElementById('kategori_edit').value = data.kategori;
                            document.querySelector('#edit input[name="harga"]').value = data.harga;

                            // Trigger change event untuk kategori agar sub kategori terisi
                            changeKategoriEdit();

                            // Set sub kategori value setelah options dibuat
                            setTimeout(function() {
                                document.getElementById('sub_kategori_edit').value = data.sub_kategori;
                            }, 100);
                        }
                    </script>
                    <div class="form-group">
                        <label for="nama">Harga</label>
                        <input type="number" class="form-control" name="harga" required>
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

<script type="text/javascript">
    // Function untuk handle perubahan kategori di modal EDIT
    function changeKategoriEdit() {
        var kategori = document.getElementById('kategori_edit').value;
        var subKategori = document.getElementById('sub_kategori_edit');

        // Clear existing options
        subKategori.innerHTML = '<option value="">-- Pilih Sub Kategori --</option>';

        // Enable sub kategori
        subKategori.disabled = false;

        if (kategori === 'produksi') {
            subKategori.innerHTML += '<option value="bahan_baku">Bahan Baku</option>';
            subKategori.innerHTML += '<option value="penolong/alat">Penolong/Alat</option>';
            subKategori.innerHTML += '<option value="produk_jadi">Produk Jadi</option>';
        } else if (kategori === 'jadi') {
            subKategori.innerHTML += '<option value="barang_jadi">Barang Jadi</option>';
        } else {
            subKategori.disabled = true;
        }
    }

    // Set initial state untuk edit modal
    document.addEventListener('DOMContentLoaded', function() {
        var subKategoriEdit = document.getElementById('sub_kategori_edit');
        if (subKategoriEdit) {
            // Hanya disable jika kategori belum dipilih
            var kategoriEdit = document.getElementById('kategori_edit');
            if (!kategoriEdit.value) {
                subKategoriEdit.disabled = true;
            }
        }
    });
</script>
