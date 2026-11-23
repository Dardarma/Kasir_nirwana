@extends('layouts.app')
@section('content-header')
@endsection
@section('content')
    <div class="row mb-2 transaction-layout">
        <div class="col-7 transaction-summary">
            <div class="card mt-1">
                <div class="card-body">
                    <table>
                        <tr>
                            <td style="width: 30%">
                                Customer
                            </td>
                            <td>:</td>
                            <td style="width: 70%">
                                <select class="form-control" id="customer" name="customer" onchange="updateBarangByCustomer()" required>
                                    <option value="">Pilih Customer</option>
                                    @if ($customers->isEmpty())
                                        <option value="">Tidak ada customer tersedia</option>
                                    @else
                                        @foreach ($customers as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 30%">
                                Metode Pembayaran
                            </td>
                            <td>:</td>
                            <td style="width: 70%">
                                <select class="form-control" id="metode_pembayaran" name="metode_pembayaran" required>
                                    <option value="">Pilih Metode Pembayaran</option>
                                    <option value="tunai">Tunai</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </td>
                        </tr>
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

                    <div class="table-responsive mt-1" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-bordered table-hover">
                            <thead style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 10;">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Harga</th>
                                    <th>Sub Total</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <!-- Data akan ditambahkan secara dinamis -->
                            </tbody>
                            <tfoot style="position: sticky; bottom: 0; background-color: #f8f9fa; z-index: 10;">
                                <tr>
                                    <td colspan="5" class="text-right"><strong>Total</strong></td>
                                    <td><strong id="grand-total">Rp 0</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="bayar">Customer bayar</label>
                                <input type="number" class="form-control" id="bayar" name="bayar" 
                                       oninput="hitungKembalian()" min="0" step="0.01" value="0" required>
                                <small class="text-muted">Masukkan 0 jika belum ada pembayaran</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="kembalian">Kembalian</label>
                                <input type="text" class="form-control" id="kembalian" name="kembalian" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="customer_bayar_hidden">Nilai Customer Bayar</label>
                                <input type="text" class="form-control" id="customer_bayar_display" readonly>
                                <input type="hidden" id="customer_bayar" name="customer_bayar">
                                <small class="text-muted">Status pembayaran akan ditentukan otomatis berdasarkan jumlah bayar</small>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
        <div class="col-5 transaction-entry-panel" style="background-color: #E4E4F2; border-radius: 10px; padding: 20px;">
            <h2>Kasir</h2>
            <div class="" style="margin-top: 50px;">
                <div class="form-group">
                    <label for="barang">Barang</label>
                    <select class="form-control select2" name="sub_barang" id="sub_barang" onchange="updateSatuan()" disabled required>
                        <option value="">Pilih Customer terlebih dahulu</option>
                    </select>
                    <input type="hidden" id="barang_id" name="barang_id" >
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="harga">Harga</label>
                            <input type="number" class="form-control" name="harga" id="harga"
                                oninput="updateSubTotal()" min="0" max="2147483647" step="1" readonly required>
                        </div>
                    </div>
                    <div class="col-9">
                        <div class="form-group">
                            <label for="jumlah">Jumlah</label>
                            <input type="number" class="form-control" name="jumlah" id="jumlah" oninput="updateSubTotal()"
                                min="0.01" required step="0.01">
                            <input type="hidden" id="gramasi" name="gramasi">
                            <input type="hidden" id="jumlah_gram" name="jumlah_gram">
                            <small class="text-muted" id="stok-info">Pilih barang untuk melihat stok</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="satuan">Satuan</label>
                            <input type="text" class="form-control" id="satuan" name="satuan" readonly required>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="sub_total">Sub Total</label>
                            <input type="text" class="form-control" name="sub_total" id="sub_total" readonly required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="" style="margin-top: 40px">
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-primary" id="btn-tambah" onclick="tambahItem()">Tambah</button>
                    <a href="/kasir/list" type="button" class="btn btn-info" id="btn-laporan">Laporan</a>
                    <div class="btn-group" role="group" aria-label="Aksi transaksi">
                        <button type="button" class="btn btn-success" id="btn-simpan">Simpan</button>
                        <button type="button" class="btn btn-success mx-2" id="btn-simpan-cetak">Simpan dan Cetak</button>
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
        let grandTotal = 0;
        var item_list = [];
        var currentStok = 0; 



        // Function untuk update barang berdasarkan customer yang dipilih
        function updateBarangByCustomer() {
            var customerSelect = document.getElementById('customer');
            var barangSelect = document.getElementById('sub_barang');
            var customerId = customerSelect.value;

            
            clearForm();

            if (!customerId) {
                barangSelect.disabled = true;
                barangSelect.innerHTML = '<option value="">Pilih Customer terlebih dahulu</option>';
                return;
            }

            // Enable select barang dan loading
            barangSelect.disabled = false;
            barangSelect.innerHTML = '<option value="">Loading...</option>';

            // AJAX call untuk get barang berdasarkan customer
            $.ajax({
                url: `/kasir/barang-by-customer/${customerId}`,
                type: 'GET',
                success: function(response) {
                    if (response.status === 'success' && response.data.length > 0) {
                        barangSelect.replaceChildren(new Option('Pilih Barang', ''));
                        response.data.forEach(function(item) {
                            const option = new Option(item.sub_nama, item.id);
                            option.dataset.satuan = item.satuan;
                            option.dataset.harga = item.harga ?? '';
                            option.dataset.nonStok = item.non_stok ? '1' : '0';
                            option.dataset.idBarang = item.barang_id;
                            option.dataset.customerId = item.customer_id;
                            option.dataset.gramasi = item.faktor_satuan ?? '';
                            barangSelect.add(option);
                        });
                    } else {
                        barangSelect.innerHTML = '<option value="">Tidak ada barang untuk customer ini</option>';
                    }
                },
                error: function() {
                    barangSelect.innerHTML = '<option value="">Error loading barang</option>';
                    alert('Gagal memuat data barang');
                }
            });
        }

        function updateSatuan() {
            var select = document.getElementById('sub_barang');
            var id_barang = document.getElementById('barang_id');
            var gramasiInput = document.getElementById('gramasi');
            var satuanInput = document.getElementById('satuan');
            var hargaInput = document.getElementById('harga');
            var stokInfo = document.getElementById('stok-info');
            var jumlahInput = document.querySelector('input[name="jumlah"]');

            if (select.value === '') {
                satuanInput.value = '';
                hargaInput.value = '';
                hargaInput.readOnly = true;
                currentStok = 0;
                id_barang.value = '';
                stokInfo.textContent = 'Pilih barang untuk melihat stok';
                stokInfo.className = 'text-muted';
                jumlahInput.max = '';
                gramasiInput.value = '';
                updateSubTotal();
                return;
            }


            var selectedOption = select.options[select.selectedIndex];
            var satuan = selectedOption.getAttribute('data-satuan');
            var harga = selectedOption.getAttribute('data-harga');
            var barangId = selectedOption.getAttribute('data-id-barang');
            var subBarangId = selectedOption.value;
            var gramasi = selectedOption.getAttribute('data-gramasi');


            satuanInput.value = satuan || '';
            const nonStok = selectedOption.dataset.nonStok === '1';
            hargaInput.readOnly = !nonStok;
            hargaInput.value = nonStok ? '' : (harga || '');
            id_barang.value = barangId || '';
            gramasiInput.value = gramasi || '';


            currentStok = 0;
            jumlahInput.max = '';
            if (nonStok) {
                stokInfo.textContent = 'Item nonstok. Isi harga satuan.';
                stokInfo.className = 'text-muted';
                updateSubTotal();
                return;
            }

            stokInfo.textContent = 'Memuat stok…';
            stokInfo.className = 'text-muted';
            updateSubTotal();
            $.ajax({
                url: `/kasir/stok-realtime/${subBarangId}`,
                type: 'GET',
                success: function(response) {
                    if (select.selectedOptions[0] !== selectedOption) return;
                    if (response.status === 'success') {
                        currentStok = response.stok;
                        
                        // Update info stok dan validasi
                        if (currentStok > 0) {
                            stokInfo.textContent = `Stok tersedia: ${formatJumlah(currentStok)}`;
                            stokInfo.className = 'text-success';
                            jumlahInput.max = currentStok;
                        } else {
                            stokInfo.textContent = 'Stok kosong!';
                            stokInfo.className = 'text-danger';
                            jumlahInput.max = 0;
                        }

                        // Reset jumlah jika melebihi stok
                        if (parseFloat(jumlahInput.value) > currentStok) {
                            jumlahInput.value = '';
                        }

                        // Update subtotal setelah harga berubah
                        updateSubTotal();
                    }
                },
                error: function() {
                    if (select.selectedOptions[0] !== selectedOption) return;
                    // Fallback ke data dari option jika AJAX gagal
                    var stok = parseFloat(selectedOption.getAttribute('data-stok')) || 0;
                    currentStok = stok;
                    
                    if (stok > 0) {
                        stokInfo.textContent = `Stok tersedia: ${formatJumlah(stok)} (cached)`;
                        stokInfo.className = 'text-warning';
                        jumlahInput.max = stok;
                    } else {
                        stokInfo.textContent = 'Stok kosong!';
                        stokInfo.className = 'text-danger';
                        jumlahInput.max = 0;
                    }

                    updateSubTotal();
                }
            });
        }

        function updateSubTotal() {
            var hargaInput = document.getElementById('harga');
            var jumlahInput = document.querySelector('input[name="jumlah"]');
            var subTotalInput = document.getElementById('sub_total');
            var jumlahGramInput = document.getElementById('jumlah_gram');
            var gramasi = document.querySelector('input[name="gramasi"]');

            var harga = parseFloat(hargaInput.value) || 0;
            var jumlah = parseFloat(jumlahInput.value) || 0;
            var gramasi = parseFloat(gramasi.value) || 0;

            var jumlah_gram = gramasi * jumlah;

            // Validasi jumlah tidak melebihi stok
            if (jumlah > currentStok && currentStok > 0) {
                jumlahInput.value = currentStok;
                jumlah = currentStok;
                alert(`Jumlah tidak boleh melebihi stok yang tersedia (${formatJumlah(currentStok)})`);
            }

            var subTotal = hargaInput.readOnly ? harga * jumlah : Math.round(harga * jumlah);

            // Format sub total dengan rupiah
            subTotalInput.value = subTotal > 0 ? `Rp ${formatRupiah(subTotal)}` : '';
            jumlahGramInput.value = jumlah_gram > 0 ? jumlah_gram : '';
        }

        function tambahItem() {
            var customerSelect = document.getElementById('customer');
            if (!customerSelect.value) {
                alert('Silakan pilih customer terlebih dahulu!');
                return;
            }

            var sub_barangSelect = document.getElementById('sub_barang');
            var jumlahInput = document.querySelector('input[name="jumlah"]');
            var hargaInput = document.getElementById('harga');
            var satuan = document.getElementById('satuan').value;
            var barangId = document.getElementById('barang_id').value;
            var jumlahGram = document.getElementById('jumlah_gram').value;

            var jumlah = parseFloat(jumlahInput.value) || 0;
            var harga = parseFloat(hargaInput.value) || 0;

            if (!sub_barangSelect.value) {
                alert('Silakan pilih barang terlebih dahulu!');
                return;
            }

            if (jumlah < 0.01 || jumlahInput.validity.stepMismatch) {
                alert('Jumlah minimal 0,01 dan maksimal dua angka desimal. Pilih satuan yang lebih kecil bila diperlukan.');
                return;
            }

            var selectedOption = sub_barangSelect.options[sub_barangSelect.selectedIndex];
            const nonStok = selectedOption.dataset.nonStok === '1';
            if (nonStok ? !hargaInput.value || !hargaInput.validity.valid : harga <= 0) {
                alert(nonStok ? 'Isi harga satuan dalam rupiah (minimal 0).' : 'Harga harus lebih dari 0!');
                return;
            }

            // Validasi stok
            if (!nonStok && currentStok <= 0) {
                alert('Stok barang tidak tersedia!');
                return;
            }

            if (!nonStok && jumlah > currentStok) {
                alert(`Jumlah tidak boleh melebihi stok yang tersedia (${formatJumlah(currentStok)})`);
                return;
            }

            // Get selected option data
            var namaBarang = selectedOption.text;
            var sub_barangId = sub_barangSelect.value;
            

            // Calculate subtotal
            var subTotal = nonStok ? Math.round(jumlah * harga) : jumlah * harga;

            // Create new row
            var tbody = document.getElementById('table-body');
            var newRow = document.createElement('tr');
            newRow.setAttribute('data-id', barangId);
            newRow.setAttribute('data-subtotal', subTotal);

            newRow.innerHTML = `
                <td>${itemCounter}</td>
                <td>${escapeHtmlText(namaBarang)}</td>
                <td>${formatJumlah(jumlah)}</td>
                <td>${escapeHtmlText(satuan)}</td>
                <td>Rp ${formatRupiah(harga)}</td>
                <td class="subtotal">Rp ${formatRupiah(subTotal)}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="hapusItem(this, ${subTotal})">Hapus</button>
                </td>
            `;

            // Add row to table
            tbody.appendChild(newRow);

            // Update grand total
            grandTotal += subTotal;
            updateGrandTotal();

            // Clear form
            clearForm();

            //Add to item_list for backend
            item_list.push({
                sub_barang_id: sub_barangId,
                barang_id: barangId,
                nama_barang: namaBarang,
                jumlah: jumlah,
                satuan: satuan,
                harga: harga,
                sub_total: subTotal,
                jumlah_gram: nonStok ? 0 : jumlahGram
            });

            // Increment counter
            itemCounter++;

        }

        // Function untuk hapus item dari table
        function hapusItem(button, subTotal) {
            var row = button.closest('tr');
            var barangId = row.getAttribute('data-id');

            item_list = item_list.filter(item => item.barang_id !== barangId);

            row.remove();

            grandTotal -= subTotal;
            updateGrandTotal();

            updateRowNumbers();

        }

        function updateGrandTotal() {
            var grandTotalElement = document.getElementById('grand-total');
            if (grandTotalElement) {
                grandTotalElement.innerHTML = `Rp ${formatRupiah(grandTotal)}`;
                
                // Update kembalian dan status pembayaran setelah grand total berubah
                hitungKembalian();
            }
        }

        // Function untuk hitung kembalian dan update status pembayaran
        function hitungKembalian() {
            var bayarInput = document.getElementById('bayar');
            var kembalianInput = document.getElementById('kembalian');
            var customerBayarHidden = document.getElementById('customer_bayar');
            var customerBayarDisplay = document.getElementById('customer_bayar_display');

            var bayar = parseFloat(bayarInput.value) || 0;
            
            // Hitung kembalian
            var kembalian = bayar - grandTotal;
            
            // Update display kembalian
            if (bayar > 0) {
                if (kembalian >= 0) {
                    kembalianInput.value = `Rp ${formatRupiah(kembalian)}`;
                } else {
                    kembalianInput.value = `Kurang Rp ${formatRupiah(Math.abs(kembalian))}`;
                }
            } else {
                kembalianInput.value = '';
            }

            // Logic untuk customer_bayar
            var customerBayarValue;
            if (grandTotal > bayar) {
                // Jika grand total > bayar, maka customer bayar = bayar (hutang)
                customerBayarValue = bayar;
            } else {
                // Jika grand total <= bayar, maka customer bayar = grand total
                customerBayarValue = grandTotal;
            }
            
            customerBayarHidden.value = customerBayarValue;
            customerBayarDisplay.value = `Rp ${formatRupiah(customerBayarValue)}`;

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
            document.getElementById('sub_barang').value = '';
            document.querySelector('input[name="jumlah"]').value = '';
            document.getElementById('harga').value = '';
            document.getElementById('harga').readOnly = true;
            document.getElementById('satuan').value = '';
            document.getElementById('sub_total').value = '';
            document.getElementById('barang_id').value = '';
            document.getElementById('jumlah_gram').value = '';
            document.getElementById('gramasi').value = '';

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
            grandTotal = 0;
            var subtotalCells = document.querySelectorAll('.subtotal');

            subtotalCells.forEach(function(cell) {
                var value = cell.innerHTML.replace(/[^\d]/g, ''); // Remove non-digits
                grandTotal += parseFloat(value) || 0;
            });

            updateGrandTotal();
        }

        // Function untuk refresh semua data stok setelah transaksi
        function refreshStokData() {
            // Update stok data di select options
            $('#barang option').each(function() {
                var barangId = $(this).val();
                if (barangId) {
                    $.ajax({
                        url: `/kasir/stok-realtime/${barangId}`,
                        type: 'GET',
                        success: function(response) {
                            if (response.status === 'success') {
                                // Update data-stok attribute
                                $(`#barang option[value="${barangId}"]`).attr('data-stok', response.stok);
                            }
                        },
                        error: function() {
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
            grandTotal = 0;
            updateGrandTotal();

            // Reset form fields
            document.getElementById('customer').value = '';
            document.getElementById('metode_pembayaran').value = '';
            document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
            document.getElementById('bayar').value = '0'; // Set default ke 0
            document.getElementById('kembalian').value = '';
            document.getElementById('customer_bayar').value = '';
            document.getElementById('customer_bayar_display').value = '';
            

            // Reset barang select ke disabled state
            var barangSelect = document.getElementById('sub_barang');
            barangSelect.disabled = true;
            barangSelect.innerHTML = '<option value="">Pilih Customer terlebih dahulu</option>';

            // Clear form input
            clearForm();

            // Refresh stok data setelah transaksi berhasil
            refreshStokData();

        }

        // Event listener untuk tombol tambah
        document.addEventListener('DOMContentLoaded', function() {

            // Reset grand total saat load
            grandTotal = 0;
            updateGrandTotal();

            // Add event listeners untuk real-time calculation
            var jumlahInput = document.querySelector('input[name="jumlah"]');
            var hargaInput = document.getElementById('harga');
            var bayarInput = document.getElementById('bayar');

            if (jumlahInput) {
                jumlahInput.addEventListener('input', updateSubTotal);
                jumlahInput.addEventListener('change', updateSubTotal);
            }

            if (hargaInput) {
                hargaInput.addEventListener('input', updateSubTotal);
                hargaInput.addEventListener('change', updateSubTotal);
            }

            if (bayarInput) {
                bayarInput.addEventListener('input', hitungKembalian);
                bayarInput.addEventListener('change', hitungKembalian);
            }


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

        //backend push itemList to backend
        //push Back End
        $(document).ready(function() {
            $('#btn-simpan, #btn-simpan-cetak').on('click', function() {
                const cetakNota = this.id === 'btn-simpan-cetak';

                // Validasi data
                if (item_list.length === 0) {
                    alert('Silakan tambahkan item terlebih dahulu!');
                    return;
                }

                var tanggal = document.getElementById('tanggal').value;
                var customerBayar = document.getElementById('customer_bayar').value;
                var customerId = document.getElementById('customer').value;
                var bayar = document.getElementById('bayar').value;
                var metodePembayaran = document.getElementById('metode_pembayaran').value;
                var total = grandTotal;

                if (!customerId) {
                    alert('Silakan pilih customer terlebih dahulu!');
                    return;
                }

                // Izinkan bayar 0 atau lebih
                if (bayar === '' || parseFloat(bayar) < 0) {
                    alert('Jumlah bayar tidak boleh kosong atau negatif!');
                    return;
                }

                if (!total || total <= 0) {
                    alert('Total harus lebih dari 0!');
                    return;
                }

                if (!tanggal) {
                    alert('Silakan isi tanggal!');
                    return;
                }

                if (!metodePembayaran) {
                    alert('Silakan pilih metode pembayaran!');
                    return;
                }

                let data = {
                    items: item_list,
                    tanggal: tanggal,
                    customer_id: customerId,
                    total: total,
                    bayar: bayar,
                    metode_pembayaran: metodePembayaran,
                    customer_bayar: customerBayar,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };


                $.ajax({
                    url: '/kasir',
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    beforeSend: function() {
                        $('#btn-simpan, #btn-simpan-cetak').prop('disabled', true);
                        $(cetakNota ? '#btn-simpan-cetak' : '#btn-simpan').text('Menyimpan...');
                    },
                    success: function(response) {
                        alert('Data berhasil disimpan!');

                        // Reset form dan table
                        resetForm();
                        if (cetakNota && response.transaksi_id) {
                            window.location.href = `/kasir/${response.transaksi_id}/nota`;
                        }
                    },
                    error: function(xhr, status, error) {
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
                        $('#btn-simpan, #btn-simpan-cetak').prop('disabled', false);
                        $('#btn-simpan').text('Simpan');
                        $('#btn-simpan-cetak').text('Simpan dan Cetak');
                    }
                });
            });
        });
    </script>
@endsection
