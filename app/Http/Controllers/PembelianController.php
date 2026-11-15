<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use App\Models\Transaksi;
use App\Models\Barang;
use App\Models\Satuan;
use App\Models\Stok_masuk;
use App\Models\Transaksi_detail;

use App\Http\Requests\ConfirmTransactionDeletion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
  public function index()
  {
    $barang = get_barang_produksi();

    return view('pembelian.index', ['barang' => $barang, 'conversions' => Satuan::konversiStok()]);
  }

  public function store(Request $request)
  {
    try {
      $request->validate([
        'tanggal' => 'required|date',
        'bayar' => 'required|numeric|min:0', // Izinkan bayar 0
        'customer_bayar' => 'required|numeric|min:0',
        'metode_pembayaran' => 'required|in:tunai,transfer,qris',
        'items' => 'required|array',
        'items.*.barang_id' => 'required|exists:barang,id',
        'items.*.nama_barang' => 'required|string|max:255',
        'items.*.jumlah' => 'required|numeric|min:0.01|decimal:0,2|max:999999999',
        'items.*.satuan' => 'required|exists:satuan,nama',
        'items.*.harga' => 'required|numeric|min:0',
      ], ['items.*.jumlah.decimal' => 'Jumlah maksimal dua angka desimal. Pilih satuan yang lebih kecil bila diperlukan.', 'items.*.jumlah.min' => 'Jumlah minimal 0,01. Pilih satuan yang lebih kecil bila diperlukan.']);

      // Logic status pembayaran di backend
      $customerBayar = $request->customer_bayar;
      $items = $request->items;
      $total = 0;
      foreach ($items as $index => $item) {
        $items[$index]['sub_total'] = $item['jumlah'] * $item['harga'];
        $total += $items[$index]['sub_total'];
      }
      $statusPembayaran = ($customerBayar >= $total) ? 'lunas' : 'belum_lunas'; // Kembali ke nilai enum yang benar
      if ($statusPembayaran === 'lunas') {
          $tanggal_pembayaran = Carbon::now()->format('Y-m-d');
      } else {
          $tanggal_pembayaran = null; // Atau bisa diisi dengan nilai default lainnya
      }

      // create kode transaksi
      $kode_transaksi = Transaksi::generateCode('BELI');

      //simpan transaksi
      DB::transaction(function () use ($request, $items, $total, $statusPembayaran, $tanggal_pembayaran, $kode_transaksi) {
          $transaksi = Transaksi::create([
            'tanggal' => $request->tanggal,
            'total_bayar' => $total,
            'customer_bayar' => $request->customer_bayar,
            'status_pembayaran' => $statusPembayaran,
            'jenis_transaksi' => 'pembelian',
            'kode_transaksi' => $kode_transaksi,
            'metode_pembayaran' => $request->metode_pembayaran,
            'tanggal_pembayaran' => $tanggal_pembayaran,
            'status' => 'aktif',
          ]);

          //simpan detail transaksi
          foreach ($items as $item) {
            $barang = Barang::where('kategori', 'produksi')->where('status', 'aktif')->findOrFail($item['barang_id']);
            [$satuanStok, $factor] = Satuan::dasarStok($item['satuan']);
            if ($satuanStok !== Satuan::dasarStok($barang->satuan)[0]) {
                throw ValidationException::withMessages(['items' => 'Pilih satuan yang memiliki satuan dasar sama dengan barang.']);
            }
            $jumlahStok = round($item['jumlah'] * $factor, 2);
            if ($jumlahStok <= 0 || $jumlahStok >= 1e14) {
                throw ValidationException::withMessages(['items' => 'Hasil konversi stok minimal 0,01 dan harus dalam batas yang didukung.']);
            }
            $transaksi_detail = Transaksi_detail::create([
              'transaksi_id' => $transaksi->id,
              'barang_id' => $item['barang_id'],
              'jumlah' => $item['jumlah'],
              'jumlah_gram' => $jumlahStok,
              'satuan' => $item['satuan'],
                    'satuan_stok' => $satuanStok,
                    'faktor_satuan' => $factor,
              'nama_barang' => $item['nama_barang'],
              'harga_satuan' => $item['harga'],
              'subtotal' => $item['sub_total'],
              'kategori_barang' => 'jadi',

            ]);

            // Update stok masuk
            Stok_masuk::create([
              'barang_id' => $item['barang_id'],
              'transaksi_detail_id' => $transaksi_detail->id,
              'qty_masuk' => $jumlahStok,
            ]);
          }
      });

      return response()->json([
        'status' => 'success',
        'message' => 'Pembelian berhasil disimpan',
        'kode_transaksi' => $kode_transaksi,
        'total_items' => count($request->items)
      ]);
    } catch (ValidationException $e) {
        throw $e;
    } catch (\Exception $e) {
        report($e);
      return response()->json([
        'status' => 'error',
        'message' => 'Gagal mengambil detail transaksi'
      ], 500);
    }
  }

  public function list(Request $request)
  {
    $search = $request->input('table_search', '');
    $paginate = $request->input('paginate', 10);
    $tanggalDari = $request->input('tanggal_dari');
    $tanggalSampai = $request->input('tanggal_sampai');

    // Query untuk mendapatkan daftar pembelian
    $transaksi = Transaksi::with('transaksiDetail')
      ->where('status', 'aktif')
      ->where('jenis_transaksi', 'pembelian')
      ->when($search, function ($query, $search) {
        return $query->where('kode_transaksi', 'like', '%' . $search . '%');
      })
      ->when($tanggalDari && $tanggalSampai, function ($query) use ($tanggalDari, $tanggalSampai) {
          return $query->whereBetween('tanggal', [$tanggalDari, $tanggalSampai]);
      })
      ->orderBy('created_at', 'desc')
      ->paginate($paginate);
    // dd($transaksi);

    return view('pembelian.history', compact('transaksi'));
  }

  public function getDetail($id)
  {
    $transaksi = Transaksi::with('transaksiDetail')
        ->where('jenis_transaksi', 'pembelian')->findOrFail($id);

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

  public function editStatusPembayaran(Request $request, $id)
  {
    $transaksi = Transaksi::where('jenis_transaksi', 'pembelian')->findOrFail($id);

    try {
      return DB::transaction(function () use ($request, $transaksi) {
        $transaksi = Transaksi::lockForUpdate()->findOrFail($transaksi->id);
        if ($transaksi->status !== 'aktif') {
          return response()->json([
            'status' => 'error',
            'message' => 'Pembayaran transaksi yang dibatalkan tidak dapat diubah.',
          ], 422);
        }

        if ($transaksi->status_pembayaran === 'lunas') {
          return response()->json([
            'status' => 'error',
            'message' => 'Pembayaran sudah lunas dan tidak dapat diubah.'
          ], 422);
        }

        $request->validate([
          'customer_bayar' => 'required|numeric|min:0'
        ]);

        // Logic status pembayaran
        $customerBayar = $request->customer_bayar;
        $total = $transaksi->total_bayar;
        $statusPembayaran = ($customerBayar >= $total) ? 'lunas' : 'belum_lunas';

        if ($statusPembayaran === 'lunas') {
          $tanggal_pembayaran = Carbon::now()->format('Y-m-d');
        } else {
          $tanggal_pembayaran = null; // Atau bisa diisi dengan nilai default lainnya
        }

        $transaksi->update([
          'customer_bayar' => $customerBayar,
          'status_pembayaran' => $statusPembayaran,
          'tanggal_pembayaran' => $tanggal_pembayaran
        ]);

        return response()->json([
          'status' => 'success',
          'message' => 'Status pembayaran berhasil diupdate',
          'data' => $transaksi
        ]);
      });
    } catch (ValidationException $e) {
      throw $e;
    } catch (\Exception $e) {
        report($e);
      return response()->json([
        'status' => 'error',
        'message' => 'Gagal update status pembayaran'
      ], 500);
    }
  }

  public function destroy(ConfirmTransactionDeletion $request, $id)
  {
    $transaksi = Transaksi::where('jenis_transaksi', 'pembelian')->findOrFail($id);

    try {
      DB::transaction(function () use ($transaksi) {
        $transaksi = Transaksi::lockForUpdate()->findOrFail($transaksi->id);
        $barangIds = $transaksi->transaksiDetail()->lockForUpdate()->pluck('barang_id');
        Barang::whereIn('id', $barangIds)->orderBy('id')->lockForUpdate()->get();

        $stokMasuk = Stok_masuk::whereIn('transaksi_detail_id', $transaksi->transaksiDetail()->select('id'));
        $jumlahPerBarang = (clone $stokMasuk)
          ->selectRaw('barang_id, SUM(qty_masuk) AS jumlah')
          ->groupBy('barang_id')
          ->pluck('jumlah', 'barang_id');

        foreach ($jumlahPerBarang as $barangId => $jumlah) {
          if (round(get_stok_by_id($barangId) - $jumlah, 2) < 0) {
            throw ValidationException::withMessages([
              'konfirmasi_hapus' => 'Pembelian tidak dapat dibatalkan karena stok tersisa tidak mencukupi.',
            ]);
          }
        }

        $stokMasuk->delete();
        $transaksi->update(['status' => 'dibatalkan']);
      });

      return redirect()->back()->with('success', 'Transaksi pembelian berhasil dibatalkan');
    } catch (ValidationException $e) {
      throw $e;
    } catch (\Exception $e) {
        report($e);
      return redirect()->back()->with('error', 'Gagal membatalkan transaksi');
    }
  }
}
