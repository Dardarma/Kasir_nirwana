@extends('layout')
@section('content-header')
@endsection
@section('content')
    <div class="row mb-2">
        <div class="col-7">
            <div class="card mt-1">
                <div class="card-body">
                    <form method="post" action="{{ route('produksi.store') }}" id="produksi-form">
                        @csrf
                        <table>
                            <tr>
                                <td style="width: 30%">
                                    Tanggal
                                </td>
                                <td>:</td>
                                <td style="width: 70%">
                                    <input type="date" class="form-control" id="tanggal" name="tanggal"
                                        value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                                </td>
                            </tr>
                        </table>
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-bordered table-hover mt-1">
                                <thead style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 10;">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barang</th>
                                        <th>Jumlah</th>
                                        <th>Satuan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="table-body">
                                    <!-- Data akan ditambahkan secara dinamis -->
                                </tbody>
                                <tfoot style="position: sticky; bottom: 0; background-color: #f8f9fa; z-index: 10;">
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Total Stok</strong></td>
                                        <td><strong id="total">0</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-5" style="background-color: #E4E4F2; border-radius: 10px; padding: 20px;">
        <h2>Produksi</h2>
        <div class="" style="margin-top: 50px;">
            <div class="form-group">
                <label for="barang">Barang</label>
                <select class="form-control select2" name="barang" id="barang" required>
                    <option value="">Pilih Barang</option>
                    @if ($barang->isEmpty())
                        <option value="">Tidak ada barang tersedia</option>
                    @else
                        @foreach ($barang as $item)
                            <option value="{{ $item->id }}" data-satuan="{{ $item->satuan }}"
                                data-harga="{{ $item->harga }}">
                                {{ $item->nama_barang }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="row">
                <div class="col-9">
                    <div class="form-group">
                        <label for="jumlah">Jumlah</label>
                        <input type="number" class="form-control" name="jumlah" required>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="satuan">Satuan</label>
                        <input type="text" class="form-control" id="satuan" name="satuan" readonly required>
                    </div>
                </div>
            </div>
        </div>
        <div class="" style="margin-top: 40px">
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-primary" id="btn-tambah">Tambah</button>
                <a href="{{url('/produksi/list')}}" type="button" class="btn btn-info" id="btn-laporan">Laporan</a>
                <button type="button" class="btn btn-success" id="btn-simpan">Simpan</button>
            </div>
        </div>
    </div>
    </div>
    @endsection
    @section('script')
    <script>
        // Global variables
        let itemCounter = 1;
        let totalStok = 0;
        var item_list = [];

        // Function untuk update satuan (vanilla JavaScript)
        function updateSatuan() {
            var select = document.getElementById('barang');
            var satuanInput = document.getElementById('satuan');

            if (select.value === '') {
                satuanInput.value = '';
                return;
            }

            var selectedOption = select.options[select.selectedIndex];
            var satuan = selectedOption.getAttribute('data-satuan');

            satuanInput.value = satuan || '';
        }

        // Function untuk tambah item ke table
        function tambahItem() {
            console.log('Tombol tambah diklik'); // Debug

            // Get form values
            var barangSelect = document.getElementById('barang');
            var jumlahInput = document.querySelector('input[name="jumlah"]');
            var satuan = document.getElementById('satuan').value;

            var jumlah = parseFloat(jumlahInput.value) || 0;

            console.log('Barang:', barangSelect.value, 'Jumlah:', jumlah); // Debug

            // Validation
            if (!barangSelect.value) {
                alert('Silakan pilih barang terlebih dahulu!');
                return;
            }

            if (jumlah <= 0) {
                alert('Jumlah harus lebih dari 0!');
                return;
            }

            // Get selected option data
            var selectedOption = barangSelect.options[barangSelect.selectedIndex];
            var namaBarang = selectedOption.text;
            var barangId = barangSelect.value;

            // Create new row
            var tbody = document.getElementById('table-body');
            var newRow = document.createElement('tr');
            newRow.setAttribute('data-id', barangId);
            newRow.setAttribute('data-jumlah', jumlah);

            newRow.innerHTML = `
                <td>${itemCounter}</td>
                <td>${namaBarang}</td>
                <td>${jumlah}</td>
                <td>${satuan}</td>
                <td>
                    <button class="btn btn-danger btn-sm btn-hapus" data-jumlah="${jumlah}" data-id="${barangId}">Hapus</button>
                </td>
            `;

            // Add row to table
            tbody.appendChild(newRow);

            // Add event listener untuk tombol hapus yang baru
            const btnHapus = newRow.querySelector('.btn-hapus');
            if (btnHapus) {
                btnHapus.addEventListener('click', function() {
                    const jumlahHapus = parseFloat(this.getAttribute('data-jumlah'));
                    hapusItem(this, jumlahHapus);
                });
            }

            // Update total stok
            totalStok += jumlah;
            updateTotalStok();

            // Clear form
            clearForm();

            // Add to item_list for backend
            item_list.push({
                barang_id: barangId,
                nama_barang: namaBarang,
                jumlah: jumlah,
                satuan: satuan,
            });

            console.log('Item added to list:', item_list); // Debug

            // Increment counter
            itemCounter++;

            console.log('Item berhasil ditambahkan, Total Stok:', totalStok); // Debug
        }

        // Function untuk hapus item dari table
        function hapusItem(button, jumlah) {
            // Get row data before removing
            var row = button.closest('tr');
            var barangId = row.getAttribute('data-id');
            
            // Remove from item_list array
            item_list = item_list.filter(item => item.barang_id !== barangId);
            
            // Remove row
            row.remove();

            // Update total stok
            totalStok -= jumlah;
            updateTotalStok();

            // Update row numbers
            updateRowNumbers();
            
            console.log('Item removed, current list:', item_list); // Debug
        }

        // Function untuk update total stok
        function updateTotalStok() {
            var totalElement = document.getElementById('total');
            if (totalElement) {
                totalElement.innerHTML = totalStok;
                console.log('Total stok updated:', totalStok); // Debug
            } else {
                console.error('Element total tidak ditemukan'); // Debug
            }
        }

        // Function untuk update nomor urut
        function updateRowNumbers() {
            var rows = document.querySelectorAll('#table-body tr');
            rows.forEach(function(row, index) {
                row.cells[0].innerHTML = index + 1;
            });
            itemCounter = rows.length + 1;
        }

        // Function untuk clear form setelah tambah item
        function clearForm() {
            document.getElementById('barang').value = '';
            document.querySelector('input[name="jumlah"]').value = '';
            document.getElementById('satuan').value = '';

            // Trigger change untuk select2 jika ada
            if (typeof $ !== 'undefined' && $('#barang').hasClass('select2')) {
                $('#barang').val('').trigger('change');
            }
        }



        // Function untuk recalculate total stok (jika ada perubahan manual)
        function recalculateTotalStok() {
            totalStok = 0;
            var rows = document.querySelectorAll('#table-body tr');

            rows.forEach(function(row) {
                var jumlah = parseFloat(row.getAttribute('data-jumlah')) || 0;
                totalStok += jumlah;
            });

            updateTotalStok();
        }

        // Event listener
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded'); // Debug

            // Reset total stok saat load
            totalStok = 0;
            updateTotalStok();

            // Event listener untuk select barang
            const barangSelect = document.getElementById('barang');
            if (barangSelect) {
                barangSelect.addEventListener('change', updateSatuan);
                
                // For Select2
                if (typeof $ !== 'undefined') {
                    $(barangSelect).on('change', updateSatuan);
                }
            }

            // Event listener untuk tombol tambah
            const btnTambah = document.getElementById('btn-tambah');
            if (btnTambah) {
                btnTambah.addEventListener('click', tambahItem);
            }

            console.log('Initialization complete'); // Debug
        });

        // Handle Enter key untuk quick add
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                var activeElement = document.activeElement;
                if (activeElement.name === 'jumlah') {
                    e.preventDefault();
                    tambahItem();
                }
            }
        });

        //push Back End
        $(document).ready(function() {
            $('#btn-simpan').on('click', function() {
                console.log('Tombol simpan diklik'); // Debug
                console.log('Data yang akan dikirim:', item_list); // Debug
                
                // Validasi data
                if (item_list.length === 0) {
                    alert('Silakan tambahkan item terlebih dahulu!');
                    return;
                }
                
                var tanggal = document.getElementById('tanggal').value;
                if (!tanggal) {
                    alert('Silakan isi tanggal!');
                    return;
                }

                let data = {
                    items: item_list, // Ubah dari 'item' ke 'items' sesuai controller
                    tanggal: tanggal,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                console.log('Final data:', data); // Debug

                $.ajax({
                    url: '{{ url("/produksi") }}',
                    type: 'POST',
                    data: data,
                    beforeSend: function() {
                        $('#btn-simpan').prop('disabled', true).text('Menyimpan...');
                    },
                    success: function(response) {
                        console.log('Response:', response); // Debug
                        alert('Data berhasil disimpan!');
                        
                        // Reset form dan table
                        resetForm();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', xhr.responseText); // Debug
                        let errorMessage = 'Gagal menyimpan data';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join(', ');
                        }
                        
                        alert(errorMessage);
                    },
                    complete: function() {
                        $('#btn-simpan').prop('disabled', false).text('Simpan');
                    }
                });
            });
        });

        // Function untuk reset form setelah berhasil simpan
        function resetForm() {
            // Clear table
            document.getElementById('table-body').innerHTML = '';
            
            // Reset variables
            item_list = [];
            itemCounter = 1;
            totalStok = 0;
            updateTotalStok();
            
            // Reset form tanggal ke hari ini
            document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
            
            // Clear form input
            clearForm();
            
            console.log('Form reset complete'); // Debug
        }
    </script>

@endsection
