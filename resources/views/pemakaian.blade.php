@extends('layout')
@section('content-header')
@endsection
@section('content')
    <div class="row mb-2">
        <div class="col-7">
            <div class="card mt-1">
                <div class="card-header">
                    <h3 class="card-title">Data Pemakaian</h3>
                </div>
                <div class="card-body">
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

                    <div class="table-responsive mt-3" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-bordered table-hover">
                            <thead style="position: sticky; top: 0;  z-index: 10;">
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 45%;">Nama Barang</th>
                                    <th style="width: 15%;">Jumlah</th>
                                    <th style="width: 15%;">Satuan</th>
                                    <th style="width: 20%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <!-- Data akan ditambahkan secara dinamis -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-5">
            <div class="card mt-1" style="">
                <div class="card-header" style="">
                    <h3 class="card-title">Form Pemakaian</h3>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <div class="form-group">
                        <label for="barang">Barang</label>
                        <select class="form-control select2" name="barang" id="barang" onchange="updateSatuan()"
                            required>
                            <option value="">Pilih Barang</option>
                            @if ($barang->isEmpty())
                                <option value="">Tidak ada barang tersedia</option>
                            @else
                                @foreach ($barang as $item)
                                    <option value="{{ $item->id }}" data-satuan="{{ $item->satuan }}"
                                        data-stok="{{ get_stok()->firstWhere('id', $item->id)->stok ?? 0 }}">
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
                                <input type="number" class="form-control" name="jumlah"
                                    min="1" required>
                                <small class="text-muted" id="stok-info">Pilih barang untuk melihat stok</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for="satuan">Satuan</label>
                                <input type="text" class="form-control" id="satuan" name="satuan" readonly required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-primary" id="btn-tambah"
                                onclick="tambahItem()">
                                Tambah
                            </button>
                            <a href="{{url('/pemakaian/list')}}" type="button" class="btn btn-info" id="btn-laporan">Laporan</a>
                            <button type="button" class="btn btn-success" id="btn-simpan">
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @section('script')
        <script>
            // Global variables
            let itemCounter = 1;
            var item_list = [];
            var currentStok = 0; // Variable untuk menyimpan stok barang yang dipilih

            // Function untuk update satuan dan harga (vanilla JavaScript)
            function updateSatuan() {
                var select = document.getElementById('barang');
                var satuanInput = document.getElementById('satuan');
                var stokInfo = document.getElementById('stok-info');
                var jumlahInput = document.querySelector('input[name="jumlah"]');

                if (select.value === '') {
                    satuanInput.value = '';
                    currentStok = 0;
                    stokInfo.textContent = 'Pilih barang untuk melihat stok';
                    stokInfo.className = 'text-muted';
                    jumlahInput.max = '';
                    return;
                }

                var selectedOption = select.options[select.selectedIndex];
                var satuan = selectedOption.getAttribute('data-satuan');
                var barangId = select.value;

                satuanInput.value = satuan || '';

                // Get stok real-time via AJAX
                $.ajax({
                    url: `{{ url('/pemakaian/stok-realtime') }}/${barangId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'success') {
                            currentStok = response.stok;

                            // Update info stok dan validasi
                            if (currentStok > 0) {
                                stokInfo.textContent = `Stok tersedia: ${currentStok}`;
                                stokInfo.className = 'text-success';
                                jumlahInput.max = currentStok;
                            } else {
                                stokInfo.textContent = 'Stok kosong!';
                                stokInfo.className = 'text-danger';
                                jumlahInput.max = 0;
                            }

                            // Reset jumlah jika melebihi stok
                            if (parseInt(jumlahInput.value) > currentStok) {
                                jumlahInput.value = '';
                            }
                        }
                    },
                    error: function() {
                        // Fallback ke data dari option jika AJAX gagal
                        var stok = parseInt(selectedOption.getAttribute('data-stok')) || 0;
                        currentStok = stok;

                        if (stok > 0) {
                            stokInfo.textContent = `Stok tersedia: ${stok} (cached)`;
                            stokInfo.className = 'text-warning';
                            jumlahInput.max = stok;
                        } else {
                            stokInfo.textContent = 'Stok kosong!';
                            stokInfo.className = 'text-danger';
                            jumlahInput.max = 0;
                        }
                    }
                });
            }


            // Function untuk tambah item ke table
            function tambahItem() {

                // Get form values
                var barangSelect = document.getElementById('barang');
                var jumlahInput = document.querySelector('input[name="jumlah"]');
                var satuan = document.getElementById('satuan').value;

                var jumlah = parseFloat(jumlahInput.value) || 0;

                // Validation
                if (!barangSelect.value) {
                    alert('Silakan pilih barang terlebih dahulu!');
                    return;
                }

                if (jumlah <= 0) {
                    alert('Jumlah harus lebih dari 0!');
                    return;
                }

                // Validasi stok
                if (currentStok <= 0) {
                    alert('Stok barang tidak tersedia!');
                    return;
                }

                if (jumlah > currentStok) {
                    alert(`Jumlah tidak boleh melebihi stok yang tersedia (${currentStok})`);
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

                newRow.innerHTML = `
                <td>${itemCounter}</td>
                <td>${namaBarang}</td>
                <td>${jumlah}</td>
                <td>${satuan}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="hapusItem(this)">Hapus</button>
                </td>
            `;

                // Add row to table
                tbody.appendChild(newRow);

                // Clear form
                clearForm();

                //Add to item_list for backend
                item_list.push({
                    barang_id: barangId,
                    nama_barang: namaBarang,
                    jumlah: jumlah,
                    satuan: satuan,
                });

                // Increment counter
                itemCounter++;

            }

            // Function untuk hapus item dari table
            function hapusItem(button) {
                // Get row data before removing
                var row = button.closest('tr');
                var barangId = row.getAttribute('data-id');

                // Remove from item_list array
                item_list = item_list.filter(item => item.barang_id !== barangId);

                // Remove row
                row.remove();

                // Update row numbers
                updateRowNumbers();

                console.log('Item removed, current list:', item_list); // Debug
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

                // Reset stok info
                currentStok = 0;
                document.getElementById('stok-info').textContent = 'Pilih barang untuk melihat stok';
                document.getElementById('stok-info').className = 'text-muted';
                document.querySelector('input[name="jumlah"]').max = '';

                // Trigger change untuk select2 jika ada
                if (typeof $ !== 'undefined' && $('#barang').hasClass('select2')) {
                    $('#barang').val('').trigger('change');
                }
            }

            // Function untuk format rupiah
            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }

            // Function untuk hitung ulang total (jika ada perubahan manual)
            function recalculateTotal() {
                // Function ini tidak diperlukan untuk pemakaian karena tidak ada total hitung
                console.log('Recalculate total not needed for pemakaian'); // Debug
            }

            // Function untuk refresh semua data stok setelah transaksi
            function refreshStokData() {
                // Update stok data di select options
                $('#barang option').each(function() {
                    var barangId = $(this).val();
                    if (barangId) {
                        $.ajax({
                            url: `{{ url('/pemakaian/stok-realtime') }}/${barangId}`,
                            type: 'GET',
                            success: function(response) {
                                if (response.status === 'success') {
                                    // Update data-stok attribute
                                    $(`#barang option[value="${barangId}"]`).attr('data-stok', response
                                        .stok);
                                }
                            },
                            error: function() {
                                console.log(`Failed to refresh stok for barang ID: ${barangId}`);
                            }
                        });
                    }
                });
            }

            // Function untuk reset form setelah berhasil simpan
            function resetForm() {
                // Clear table
                document.getElementById('table-body').innerHTML = '';

                // Reset variables
                item_list = [];
                itemCounter = 1;

                // Reset form tanggal ke hari ini
                document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];

                // Clear form input
                clearForm();

                // Refresh stok data setelah transaksi berhasil
                refreshStokData();

                console.log('Form reset complete with stok refresh'); // Debug
            }

            // Event listener untuk tombol tambah
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM loaded'); // Debug

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
                        items: item_list,
                        tanggal: tanggal,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };

                    console.log('Final data:', data); // Debug

                    $.ajax({
                        url: '{{ url("/pemakaian") }}',
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
        </script>
    @endsection
