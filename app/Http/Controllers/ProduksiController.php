<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Stok_masuk;
use App\Models\Transaksi;
use App\Models\Transaksi_detail;
use Carbon\Carbon;

class ProduksiController extends Controller
{
    public function index(){

        $barang = get_barang_jadi();

        return view('produksi', compact('barang'));

    }

    public function store(Request $request)
    {
        try{
            $request->validate([
                'tanggal' => 'required|date',
                'items' => 'required|array',
                'items.*.barang_id' => 'required|exists:barang,id',
                'items.*.jumlah' => 'required|numeric|min:1',
                'items.*.satuan' => 'required|string|max:50',
                'items.*.nama_barang' => 'required|string|max:255',
            ]);

            $tanggal_now = Carbon::now()->format('Ymd');

            $jumlah_trasaksi_today = Transaksi::whereDate('created_at', Carbon::today())->count();
            $kode_transaksi = 'PROD' . $tanggal_now . str_pad($jumlah_trasaksi_today + 1, 4, '0', STR_PAD_LEFT);

            $transaksi=Transaksi::create([
                'tanggal' => $request->tanggal,
                'status' => 'aktif',
                'jenis_transaksi' => 'produksi',
                'kode_transaksi' => $kode_transaksi
            ]);

            foreach ($request->items as $item){
                $transaksi_detail =Transaksi_detail::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                    'satuan' => $item['satuan'],
                    'kategori_barang' => 'jadi',
                    'nama_barang' => $item['nama_barang'],
                ]);

                Stok_masuk::create([
                    'barang_id' => $item['barang_id'],
                    'transaksi_detail_id' => $transaksi_detail->id,
                    'qty_masuk' => $item['jumlah'],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data produksi berhasil disimpan',
                'kode_transaksi' => $kode_transaksi,
                'total_items' => count($request->items)
            ]);

        }catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data produksi: ' . $e->getMessage()
            ], 400);
        }
    }

     public function list(Request $request)
    {
        $search = $request->input('search', '');
        $paginate = $request->input('paginate', 10);

        $transaksi = Transaksi::with('transaksiDetail')
            ->where('jenis_transaksi', 'produksi')
            ->where('status', 'aktif')
            ->orderBy('created_at', 'desc')
            ->paginate($paginate);

        return view('list.produksi_list', compact('transaksi'));
    }

    public function getDetail($id)
    {
        try {
            $transaksi = Transaksi::with('transaksi_detail')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $transaksi->transaksi_detail,
                'transaksi' => $transaksi
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mendapatkan detail transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $transaksi = Transaksi::findOrFail($id);
            $transaksi->status = 'dibatalkan';
            $transaksi->save();

            // Hapus stok masuk yang terkait
            Stok_masuk::where('transaksi_detail_id', $transaksi->id)->delete();

            return redirect()->back()->with('success', 'Transaksi ' . $transaksi->kode_transaksi . ' berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
