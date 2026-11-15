<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmTransactionDeletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Transaksi;
use App\Models\Transaksi_detail;
use App\Models\Stok_keluar;
use App\Models\Barang;
use App\Models\Satuan;
use Illuminate\Validation\ValidationException;

class ControllerPemakaian extends Controller
{
    public function index()
    {
        $barang =  Barang::where('status', 'aktif')
            ->where('kategori', 'produksi')
            ->whereNotIn('sub_kategori', ['penolong/alat', 'produk_jadi'])
            ->get();

        // Debug: lihat data barang yang di-load
        Log::info('Barang loaded for pemakaian:', $barang->toArray());

        // Debug dengan dd untuk melihat data langsung
        // dd($barang); // Uncomment ini untuk debug

        return view('pemakaian.index', ['barang' => $barang, 'conversions' => Satuan::konversiStok()]);
    }

    public function getStokRealtime(Request $request, $barang_id = null)
    {
        try {
            if (!$barang_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Barang ID tidak ditemukan'
                ], 400);
            }

            $barang = Barang::findOrFail($barang_id);
            [$base, $factor] = Satuan::dasarStok($request->query('satuan', $barang->satuan));
            if ($base !== Satuan::dasarStok($barang->satuan)[0]) {
                return response()->json(['status' => 'error', 'message' => 'Satuan pemakaian tidak valid.'], 422);
            }
            $stok = get_stok_by_id($barang_id, $factor);

            return response()->json([
                'status' => 'success',
                'stok' => $stok,
                'barang_id' => $barang_id
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting stok realtime: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data stok'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tanggal' => 'required|date',
                'items' => 'required|array',
                'items.*.barang_id' => 'required|exists:barang,id',
                'items.*.nama_barang' => 'required|string|max:255',
                'items.*.jumlah' => 'required|numeric|min:0.01|decimal:0,2|max:999999999',
                'items.*.satuan' => 'required|exists:satuan,nama',
            ], ['items.*.jumlah.decimal' => 'Jumlah maksimal dua angka desimal. Pilih satuan yang lebih kecil bila diperlukan.', 'items.*.jumlah.min' => 'Jumlah minimal 0,01. Pilih satuan yang lebih kecil bila diperlukan.']);

            // create kode transaksi
            $kode_transaksi = Transaksi::generateCode('PAKAI');

            DB::transaction(function () use ($validated, $kode_transaksi) {
                $barangIds = collect($validated['items'])->pluck('barang_id')->unique();
                $barangTerkunci = Barang::whereIn('id', $barangIds)
                    ->where('kategori', 'produksi')
                    ->where('status', 'aktif')
                    ->whereNotIn('sub_kategori', ['penolong/alat', 'produk_jadi'])
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $stokTerpakai = [];
                $items = $validated['items'];
                foreach ($items as $index => $item) {
                    $barang = $barangTerkunci->get($item['barang_id']);
                    if (! $barang) {
                        throw ValidationException::withMessages(['items' => 'Barang atau satuan pemakaian tidak valid.']);
                    }

                    [$satuanStok, $factor] = Satuan::dasarStok($item['satuan']);
                    if ($satuanStok !== Satuan::dasarStok($barang->satuan)[0]) {
                        throw ValidationException::withMessages(['items' => 'Pilih satuan yang memiliki satuan dasar sama dengan barang.']);
                    }
                    $jumlahStok = round($item['jumlah'] * $factor, 2);
                    if ($jumlahStok <= 0 || $jumlahStok >= 1e14) {
                        throw ValidationException::withMessages(['items' => 'Jumlah pemakaian tidak valid.']);
                    }

                    $stokTerpakai[$barang->id] = round(($stokTerpakai[$barang->id] ?? 0) + $jumlahStok, 2);
                    $items[$index]['jumlah_stok'] = $jumlahStok;
                    $items[$index]['satuan_stok'] = $satuanStok;
                    $items[$index]['faktor'] = $factor;
                }

                foreach ($stokTerpakai as $barangId => $jumlahStok) {
                    if ($jumlahStok > round(get_stok_by_id($barangId), 2)) {
                        throw ValidationException::withMessages([
                            'items' => "Stok {$barangTerkunci[$barangId]->nama_barang} tidak mencukupi.",
                        ]);
                    }
                }

                //simpan transaksi
                $transaksi = Transaksi::create([
                    'tanggal' => $validated['tanggal'],
                    'total_bayar' => 0, // Pemakaian tidak ada nilai bayar
                    'customer_bayar' => 0,
                    'status_pembayaran' => 'lunas', // Default lunas untuk pemakaian
                    'jenis_transaksi' => 'pemakaian',
                    'kode_transaksi' => $kode_transaksi,
                    'status' => 'aktif',
                ]);

                //simpan detail transaksi
                foreach ($items as $item) {
                    $barang = $barangTerkunci[$item['barang_id']];
                    $transaksi_detail = Transaksi_detail::create([
                        'transaksi_id' => $transaksi->id,
                        'barang_id' => $item['barang_id'],
                        'jumlah' => $item['jumlah'],
                        'jumlah_gram' => $item['jumlah_stok'],
                        'satuan' => $item['satuan'],
                        'satuan_stok' => $item['satuan_stok'],
                        'faktor_satuan' => $item['faktor'],
                        'nama_barang' => $barang->nama_barang,
                        'harga_satuan' => 0, // Pemakaian tidak ada harga
                        'subtotal' => 0, // Pemakaian tidak ada subtotal
                        'kategori_barang' => 'produksi',
                    ]);

                    // Update stok keluar
                    Stok_keluar::create([
                        'barang_id' => $item['barang_id'],
                        'transaksi_detail_id' => $transaksi_detail->id,
                        'qty_keluar' => $item['jumlah_stok'],
                    ]);
                }

            });

            return response()->json([
                'status' => 'success',
                'message' => 'Pemakaian berhasil disimpan',
                'kode_transaksi' => $kode_transaksi,
                'total_items' => count($request->items)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing pemakaian: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan pemakaian'
            ], 500);
        }
    }

    public function list(Request $request)
    {
        $search = $request->input('table_search', '');
        $paginate = $request->input('paginate', 10);
        $tanggalDari = $request->input('tanggal_dari');
        $tanggalSampai = $request->input('tanggal_sampai');

        // Query untuk mendapatkan daftar pemakaian
        $transaksi = Transaksi::with('transaksiDetail')
            ->where('status', 'aktif')
            ->where('jenis_transaksi', 'pemakaian')
            ->when($search, function ($query, $search) {
                return $query->where('kode_transaksi', 'like', '%' . $search . '%');
            })
            ->when($tanggalDari && $tanggalSampai, function ($query) use ($tanggalDari, $tanggalSampai) {
                return $query->whereBetween('tanggal', [$tanggalDari, $tanggalSampai]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($paginate);

        return view('pemakaian.history', compact('transaksi'));
    }

    public function getDetail($id)
    {
        $transaksi = Transaksi::with('transaksiDetail')
            ->where('jenis_transaksi', 'pemakaian')->findOrFail($id);

        try {
            
            return response()->json([
                'status' => 'success',
                'data' => $transaksi->transaksiDetail,
                'transaksi' => $transaksi
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail transaksi'
            ], 500);
        }
    }

    public function destroy(ConfirmTransactionDeletion $request, $id)
    {
        $transaksi = Transaksi::where('jenis_transaksi', 'pemakaian')->findOrFail($id);

        try {
            DB::transaction(function () use ($transaksi) {
                $barangIds = $transaksi->transaksiDetail()->pluck('barang_id')->unique();
                Barang::whereIn('id', $barangIds)->orderBy('id')->lockForUpdate()->get();

                Stok_keluar::whereIn('transaksi_detail_id', $transaksi->transaksiDetail()->select('id'))->delete();
                $transaksi->update(['status' => 'dibatalkan']);
            });

            return redirect()->back()->with('success', 'Transaksi pemakaian berhasil dibatalkan');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi');
        }
    }
}
