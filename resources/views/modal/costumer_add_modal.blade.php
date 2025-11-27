<div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="addModuleLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Add Costumer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('costumer.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="name">Nama Costumer</label>
                        <input type="text" class="form-control" name="name" required>
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
// Function untuk handle perubahan kategori
function changeKategori() {
    var kategori = document.getElementById('kategori').value;
    var subKategori = document.getElementById('sub_kategori');
    
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

// Set initial state
window.onload = function() {
    document.getElementById('sub_kategori').disabled = true;
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#add form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Disable submit button to prevent double submission
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Menyimpan...';
            }
        });
    }
});
</script>


