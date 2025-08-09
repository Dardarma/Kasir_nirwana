<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ControllerStok extends Controller
{
    public function index(Request $request)
    {
        $filter_kategori = $request->input('filter','semua');
        $search = $request->input('table_search', '');
        $paginate = $request->input('paginate', 10);

        // Query dengan subquery untuk efisiensi yang lebih baik
        $stok = Barang::select([
                'id',
                'nama_barang', 
                'kategori', 
                'sub_kategori',
                'satuan',
                'harga',
                // Subquery untuk stok masuk
                DB::raw('(SELECT COALESCE(SUM(qty_masuk), 0) FROM stok_masuk WHERE barang_id = barang.id) as stok_masuk'),
                // Subquery untuk stok keluar  
                DB::raw('(SELECT COALESCE(SUM(qty_keluar), 0) FROM stok_keluar WHERE barang_id = barang.id) as stok_keluar'),
                // Subquery untuk stok akhir
                DB::raw('(SELECT COALESCE(SUM(qty_masuk), 0) FROM stok_masuk WHERE barang_id = barang.id) - (SELECT COALESCE(SUM(qty_keluar), 0) FROM stok_keluar WHERE barang_id = barang.id) as stok')
            ])
            ->when($search, function ($query, $search) {
                return $query->where('nama_barang', 'like', '%' . $search . '%');
            })
            ->when($filter_kategori == 'semua', function ($query) {
                // Ketika filter semua, kecualikan barang dengan sub_kategori penolong/alat
                return $query->where('sub_kategori', '!=', 'penolong/alat');
            })
            ->when($filter_kategori != 'semua', function ($query) use ($filter_kategori) {
                return $query->where('kategori', $filter_kategori)
                           ->where('sub_kategori', '!=', 'penolong/alat');
            })
            ->where('status', 'aktif')
            ->paginate($paginate);

        return view('list.stock', compact('stok'));
    }

    public function getLabaRugi(Request $request)
    {
        try{

            $month = request()->input('month', Carbon::now()->format('Y-m'));
            $paginate = request()->input('paginate', 10);
            
            // Ambil data laba rugi berdasarkan bulan yang dipilih
            $data = DB::table('transaksi')
            ->selectRaw('DATE(tanggal) as tanggal')
            ->selectRaw('SUM(CASE WHEN jenis_transaksi = "penjualan" THEN total_bayar ELSE 0 END) as pemasukan')
            ->selectRaw('SUM(CASE WHEN jenis_transaksi = "pembelian" THEN total_bayar ELSE 0 END) as pengeluaran')
            ->selectRaw('SUM(CASE WHEN jenis_transaksi = "penjualan" THEN total_bayar ELSE 0 END) - SUM(CASE WHEN jenis_transaksi = "penjualan" THEN customer_bayar ELSE 0 END) as piutang')
            ->selectRaw('SUM(CASE WHEN jenis_transaksi = "pembelian" THEN total_bayar ELSE 0 END) - SUM(CASE WHEN jenis_transaksi = "pembelian" THEN customer_bayar ELSE 0 END) as hutang')
            ->selectRaw('SUM(CASE WHEN jenis_transaksi = "penjualan" THEN total_bayar ELSE 0 END) - SUM(CASE WHEN jenis_transaksi = "pembelian" THEN total_bayar ELSE 0 END) as laba_rugi')
            ->where('tanggal', 'like', $month . '%')
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy(DB::raw('DATE(tanggal)'), 'desc')
            ->paginate($paginate);
            // dd($data);

            return view('list.laba_rugi', compact('data'));
        }catch (\Exception $e)
        {
            return redirect()->back()->withErrors(['Gagal mengambil data laba rugi: ' . $e->getMessage()]);
        }

    }
}
