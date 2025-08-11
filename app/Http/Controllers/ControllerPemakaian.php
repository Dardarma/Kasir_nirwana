<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Transaksi;
use App\Models\Transaksi_detail;
use App\Models\Stok_keluar;
use Illuminate\Support\Carbon;
use App\Models\Barang;

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

        return view('pemakaian', compact('barang'));
    }

    public function getStokRealtime($barang_id = null)
    {
        try {
            if (!$barang_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Barang ID tidak ditemukan'
                ], 400);
            }

            $stok = get_stok_by_id($barang_id);

            return response()->json([
                'status' => 'success',
                'stok' => $stok,
                'barang_id' => $barang_id
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting stok realtime: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data stok: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'tanggal' => 'required|date',
                'items' => 'required|array',
                'items.*.barang_id' => 'required|exists:barang,id',
                'items.*.nama_barang' => 'required|string|max:255',
                'items.*.jumlah' => 'required|numeric|min:1',
                'items.*.satuan' => 'required|string|max:50',
            ]);

            // create kode transaksi
            $tanggal_now = Carbon::now()->format('Ymd');
            $jumlah_trasaksi_today = Transaksi::whereDate('created_at', Carbon::today())->count();
            $kode_transaksi = 'PAKAI' . $tanggal_now . str_pad($jumlah_trasaksi_today + 1, 4, '0', STR_PAD_LEFT);

            //simpan transaksi
            $transaksi = Transaksi::create([
                'tanggal' => $request->tanggal,
                'total_bayar' => 0, // Pemakaian tidak ada nilai bayar
                'customer_bayar' => 0,
                'status_pembayaran' => 'lunas', // Default lunas untuk pemakaian
                'jenis_transaksi' => 'pemakaian',
                'kode_transaksi' => $kode_transaksi,
                'status' => 'aktif',
            ]);

            //simpan detail transaksi
            foreach ($request->items as $item) {
                $transaksi_detail = Transaksi_detail::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                    'satuan' => $item['satuan'],
                    'nama_barang' => $item['nama_barang'],
                    'harga_satuan' => 0, // Pemakaian tidak ada harga
                    'subtotal' => 0, // Pemakaian tidak ada subtotal
                    'kategori_barang' => 'produksi',
                ]);

                // Update stok keluar
                Stok_keluar::create([
                    'barang_id' => $item['barang_id'],
                    'transaksi_detail_id' => $transaksi_detail->id,
                    'qty_keluar' => $item['jumlah'],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pemakaian berhasil disimpan',
                'kode_transaksi' => $kode_transaksi,
                'total_items' => count($request->items)
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing pemakaian: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan pemakaian: ' . $e->getMessage()
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

        return view('list.pemakaian_list', compact('transaksi'));
    }

    public function getDetail($id)
    {
        try {
            $transaksi = Transaksi::with('transaksiDetail')->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => $transaksi->transaksiDetail,
                'transaksi' => $transaksi
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $transaksi = Transaksi::findOrFail($id);
            
            $transaksi->update([
                'status' => 'dibatalkan'
            ]);

            return redirect()->back()->with('success', 'Transaksi pemakaian berhasil dibatalkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }
}
