@extends('layout')
@section('content-header')
@endsection
@section('content')
    <div class="row mb-2">
        <div class="col-7">
            <div class="card mt-1">
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

                    <div class="table-responsive mt-1" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-bordered table-hover">
                            <thead style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 10;">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Keterangan</th>
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
                                    <td colspan="6" class="text-right"><strong>Total</strong></td>
                                    <td><strong id="grand-total">Rp 0</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="bayar">Pembayaran</label>
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
                                <label for="customer_bayar_hidden">Nilai Bayar</label>
                                <input type="text" class="form-control" id="customer_bayar_display" readonly>
                                <input type="hidden" id="customer_bayar" name="customer_bayar">
                                <small class="text-muted">Status pembayaran akan ditentukan otomatis berdasarkan jumlah
                                    bayar</small>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
        <div class="col-5" style="background-color: #E4E4F2; border-radius: 10px; padding: 20px;">
            <h2>Pengeluaran</h2>
            <div class="" style="margin-top: 50px;">
                <div class="form-group">
                    <label for="barang">Barang</label>
                    <select class="form-control select2" name="barang" id="barang" onchange="updateSatuan()" required>
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
                    <div class="col-12">
                        <div class="form-group">
                            <label for="harga">Harga</label>
                            <input type="number" class="form-control" name="harga" id="harga"
                                oninput="updateSubTotal()" required>
                        </div>
                    </div>
                    <div class="col-9">
                        <div class="form-group">
                            <label for="jumlah">Jumlah</label>
                            <input type="number" class="form-control" name="jumlah" oninput="updateSubTotal()"
                                min="1" required>
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
                    <div class="col-12">
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <input type="text" class="form-control" name="keterangan" id="keterangan" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="" style="margin-top: 40px">
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-primary" id="btn-tambah"
                        onclick="tambahItem()">Tambah</button>
                    <a href="{{url('/pembelian/list')}}" type="button" class="btn btn-info" id="btn-laporan">Laporan</a>
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
        let grandTotal = 0;
        var item_list = [];

        // Function untuk update satuan dan harga (vanilla JavaScript)
        function updateSatuan() {
            var select = document.getElementById('barang');
            var satuanInput = document.getElementById('satuan');
            var hargaInput = document.getElementById('harga');

            if (select.value === '') {
                satuanInput.value = '';
                hargaInput.value = '';
                updateSubTotal();
                return;
            }

            var selectedOption = select.options[select.selectedIndex];
            var satuan = selectedOption.getAttribute('data-satuan');
            var harga = selectedOption.getAttribute('data-harga');

            satuanInput.value = satuan || '';
            hargaInput.value = harga || '';

            updateSubTotal();
        }

        // Function untuk update sub total secara dinamis
        function updateSubTotal() {
            var hargaInput = document.getElementById('harga');
            var jumlahInput = document.querySelector('input[name="jumlah"]');
            var subTotalInput = document.getElementById('sub_total');

            var harga = parseFloat(hargaInput.value) || 0;
            var jumlah = parseFloat(jumlahInput.value) || 0;

            var subTotal = harga * jumlah;

            // Format sub total dengan rupiah
            subTotalInput.value = subTotal > 0 ? `Rp ${formatRupiah(subTotal)}` : '';
        }

        // Function untuk tambah item ke table
        function tambahItem() {

            // Get form values
            var barangSelect = document.getElementById('barang');
            var jumlahInput = document.querySelector('input[name="jumlah"]');
            var hargaInput = document.getElementById('harga');
            var satuan = document.getElementById('satuan').value;
            var keterangan = document.getElementById('keterangan').value;

            var jumlah = parseFloat(jumlahInput.value) || 0;
            var harga = parseFloat(hargaInput.value) || 0;

            // Validation
            if (!barangSelect.value) {
                alert('Silakan pilih barang terlebih dahulu!');
                return;
            }

            if (jumlah <= 0) {
                alert('Jumlah harus lebih dari 0!');
                return;
            }

            if (harga <= 0) {
                alert('Harga harus lebih dari 0!');
                return;
            }

         

            // Get selected option data
            var selectedOption = barangSelect.options[barangSelect.selectedIndex];
            var namaBarang = selectedOption.text;
            var barangId = barangSelect.value;
            console.log('Nama:', namaBarang, 'Harga:', harga); // Debug

            // Calculate subtotal
            var subTotal = jumlah * harga;

            // Create new row
            var tbody = document.getElementById('table-body');
            var newRow = document.createElement('tr');
            newRow.setAttribute('data-id', barangId);
            newRow.setAttribute('data-subtotal', subTotal);

            newRow.innerHTML = `
                <td>${itemCounter}</td>
                <td>${namaBarang}</td>
                <td>${jumlah}</td>
                <td>${satuan}</td>
                <td>${keterangan}</td>
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
                barang_id: barangId,
                nama_barang: namaBarang,
                jumlah: jumlah,
                satuan: satuan,
                harga: harga,
                sub_total: subTotal,
                keterangan: keterangan
            });

            // Increment counter
            itemCounter++;

        }

        // Function untuk hapus item dari table
        function hapusItem(button, subTotal) {
            // Get row data before removing
            var row = button.closest('tr');
            var barangId = row.getAttribute('data-id');

            // Remove from item_list array
            item_list = item_list.filter(item => item.barang_id !== barangId);

            // Remove row
            row.remove();

            // Update grand total
            grandTotal -= subTotal;
            updateGrandTotal();

            // Update row numbers
            updateRowNumbers();

            console.log('Item removed, current list:', item_list); // Debug
        }

        // Function untuk update grand total
        function updateGrandTotal() {
            var grandTotalElement = document.getElementById('grand-total');
            if (grandTotalElement) {
                grandTotalElement.innerHTML = `Rp ${formatRupiah(grandTotal)}`;
                console.log('Grand total updated:', grandTotal); // Debug

                // Update kembalian dan status pembayaran setelah grand total berubah
                hitungKembalian();
            } else {
                console.error('Element grand-total tidak ditemukan'); // Debug
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
            if (kembalian >= 0) {
                kembalianInput.value = `Rp ${formatRupiah(kembalian)}`;
            } else {
                kembalianInput.value = `Rp 0 (Kurang: Rp ${formatRupiah(Math.abs(kembalian))})`;
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

            console.log('Calculation - Bayar:', bayar, 'Grand Total:', grandTotal, 'Kembalian:', kembalian,
                'Customer Bayar:', customerBayarValue); // Debug
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
            document.getElementById('harga').value = '';
            document.getElementById('satuan').value = '';
            document.getElementById('sub_total').value = '';
            document.getElementById('keterangan').value = '';

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
            document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
            document.getElementById('bayar').value = '0'; // Set default ke 0
            document.getElementById('kembalian').value = '';
            document.getElementById('customer_bayar').value = '';
            document.getElementById('customer_bayar_display').value = '';

            // Clear form input
            clearForm();

            console.log('Form reset complete'); // Debug
        }

        // Function untuk refresh stok data (placeholder)
        function refreshStokData() {
            // Placeholder function untuk refresh stok data setelah transaksi
            console.log('Refreshing stok data...');
        }

        // Event listener untuk tombol tambah
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded'); // Debug

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

        //backend push itemList to backend
        //push Back End
        $(document).ready(function() {
            $('#btn-simpan').on('click', function() {

                // Validasi data
                if (item_list.length === 0) {
                    alert('Silakan tambahkan item terlebih dahulu!');
                    return;
                }

                var tanggal = document.getElementById('tanggal').value;
                var customerBayar = document.getElementById('customer_bayar').value;
                var bayar = document.getElementById('bayar').value;
                var total = grandTotal;


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

                let data = {
                    items: item_list,
                    tanggal: tanggal,
                    total: total,
                    bayar: bayar,
                    customer_bayar: customerBayar,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                console.log('Final data:', data); // Debug

                $.ajax({
                    url: '/pembelian',
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
