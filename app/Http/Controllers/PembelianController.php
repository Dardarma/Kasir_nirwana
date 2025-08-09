<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use App\Models\Transaksi;
use App\Models\Stok_masuk;
use App\Models\Transaksi_detail;

use Illuminate\Http\Request;

class PembelianController extends Controller
{
  public function index()
  {
    $barang = get_barang_produksi();

    return view('pembelian', compact('barang'));
  }

  public function store(Request $request)
  {
    try {
      $request->validate([
        'tanggal' => 'required|date',
        'total' => 'required|numeric|min:0',
        'bayar' => 'required|numeric|min:0', // Izinkan bayar 0
        'customer_bayar' => 'required|numeric|min:0',
        'items' => 'required|array',
        'items.*.barang_id' => 'required|exists:barang,id',
        'items.*.nama_barang' => 'required|string|max:255',
        'items.*.jumlah' => 'required|numeric|min:1',
        'items.*.satuan' => 'required|string|max:50',
        'items.*.harga' => 'required|numeric|min:0',
        'items.*.sub_total' => 'required|numeric|min:0',
      ]);

      // Logic status pembayaran di backend
      $customerBayar = $request->customer_bayar;
      $total = $request->total;
      $statusPembayaran = ($customerBayar >= $total) ? 'lunas' : 'belum_lunas'; // Kembali ke nilai enum yang benar

      // create kode transaksi
      $tanggal_now = Carbon::now()->format('Ymd');
      $jumlah_trasaksi_today = Transaksi::whereDate('created_at', Carbon::today())->count();
      $kode_transaksi = 'BELI' . $tanggal_now . str_pad($jumlah_trasaksi_today + 1, 4, '0', STR_PAD_LEFT);

      //simpan transaksi
      $transaksi = Transaksi::create([
        'tanggal' => $request->tanggal,
        'total_bayar' => $request->total,
        'customer_bayar' => $request->customer_bayar,
        'status_pembayaran' => $statusPembayaran,
        'jenis_transaksi' => 'pembelian',
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
          'harga_satuan' => $item['harga'],
          'subtotal' => $item['sub_total'],
          'kategori_barang' => 'jadi',

        ]);

        // Update stok masuk
        Stok_masuk::create([
          'barang_id' => $item['barang_id'],
          'transaksi_detail_id' => $transaksi_detail->id,
          'qty_masuk' => $item['jumlah'],
        ]);
      }
      return response()->json([
        'status' => 'success',
        'message' => 'Pembelian berhasil disimpan',
        'kode_transaksi' => $kode_transaksi,
        'total_items' => count($request->items)
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Gagal mengambil detail transaksi: ' . $e->getMessage()
      ], 500);
    }
  }

  public function list(Request $request)
  {
    $search = $request->input('table_search', '');
    $paginate = $request->input('paginate', 10);

    // Query untuk mendapatkan daftar pembelian
    $transaksi = Transaksi::with('transaksiDetail')
      ->where('status', 'aktif')
      ->where('jenis_transaksi', 'pembelian')
      ->when($search, function ($query, $search) {
        return $query->where('kode_transaksi', 'like', '%' . $search . '%');
      })
      ->orderBy('created_at', 'desc')
      ->paginate($paginate);
    // dd($transaksi);

    return view('list.pembelian_list', compact('transaksi'));
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

  public function editStatusPembayaran(Request $request, $id)
  {
    try {
      $request->validate([
        'customer_bayar' => 'required|numeric|min:0'
      ]);

      $transaksi = Transaksi::findOrFail($id);
      
      // Logic status pembayaran
      $customerBayar = $request->customer_bayar;
      $total = $transaksi->total_bayar;
      $statusPembayaran = ($customerBayar >= $total) ? 'lunas' : 'belum_lunas';
      
      $transaksi->update([
        'customer_bayar' => $customerBayar,
        'status_pembayaran' => $statusPembayaran
      ]);

      return response()->json([
        'status' => 'success',
        'message' => 'Status pembayaran berhasil diupdate',
        'data' => $transaksi
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Gagal update status pembayaran: ' . $e->getMessage()
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

      return redirect()->back()->with('success', 'Transaksi pembelian berhasil dibatalkan');
    } catch (\Exception $e) {
      return redirect()->back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
    }
  }
}
