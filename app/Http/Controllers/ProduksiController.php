<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmTransactionDeletion;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Satuan;
use App\Models\Stok_masuk;
use App\Models\Transaksi;
use App\Models\Transaksi_detail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProduksiController extends Controller
{
    public function index(){

        $barang =  Barang::where('status', 'aktif')
            ->where('kategori', 'jadi')
            ->get();
    
        $satuanProduksi = Satuan::konversiStok();

        return view('produksi.index', compact('barang', 'satuanProduksi'));

    }

    public function store(Request $request)
    {
        try{
            $request->validate([
                'tanggal' => 'required|date',
                'items' => 'required|array',
                'items.*.barang_id' => 'required|exists:barang,id',
                'items.*.jumlah' => 'required|numeric|min:0.01|decimal:0,2',
                'items.*.satuan' => 'required|string|max:50',
                'items.*.nama_barang' => 'required|string|max:255',
            ], ['items.*.jumlah.decimal' => 'Jumlah maksimal dua angka desimal. Pilih satuan yang lebih kecil bila diperlukan.', 'items.*.jumlah.min' => 'Jumlah minimal 0,01. Pilih satuan yang lebih kecil bila diperlukan.']);

            $kode_transaksi = Transaksi::generateCode('PROD');

            $conversions = Satuan::konversiStok();
            $items = $request->items;
            foreach ($items as &$item) {
                $barang = Barang::where('kategori', 'jadi')->where('status', 'aktif')->findOrFail($item['barang_id']);
                $conversion = $conversions[$item['satuan']] ?? null;
                $base = $conversions[$barang->satuan]['dasar'] ?? null;
                if (! $conversion || $conversion['dasar'] !== $base) {
                    throw ValidationException::withMessages(['items' => 'Satuan produksi harus memiliki satuan dasar yang sama dengan barang.']);
                }
                $factor = $conversion['faktor'];
                $item['satuan_stok'] = $base;
                $grams = round($item['jumlah'] * ($factor ?? 0), 2);
                if ($grams <= 0 || $grams >= 1e14) {
                    throw ValidationException::withMessages(['items' => 'Satuan atau hasil konversi ke satuan dasar tidak valid.']);
                }
                $item['faktor'] = $factor;
                $item['jumlah_gram'] = $grams;
                $item['nama_barang'] = $barang->nama_barang;
            }
            unset($item);

            DB::transaction(function () use ($request, $kode_transaksi, $items) {
                $transaksi=Transaksi::create([
                    'tanggal' => $request->tanggal,
                    'status' => 'aktif',
                    'jenis_transaksi' => 'produksi',
                    'kode_transaksi' => $kode_transaksi
                ]);

                foreach ($items as $item){

                    $jumlah_gram = $item['jumlah_gram'];

                    $transaksi_detail =Transaksi_detail::create([
                        'transaksi_id' => $transaksi->id,
                        'barang_id' => $item['barang_id'],
                        'jumlah' => $item['jumlah'],
                        'jumlah_gram' => $jumlah_gram,
                        'faktor_satuan' => $item['faktor'],
                        'satuan_stok' => $item['satuan_stok'],
                        'satuan' => $item['satuan'],
                        'kategori_barang' => 'jadi',
                        'nama_barang' => $item['nama_barang'],
                    ]);

                    Stok_masuk::create([
                        'barang_id' => $item['barang_id'],
                        'transaksi_detail_id' => $transaksi_detail->id,
                        'qty_masuk' => $jumlah_gram,
                    ]);
                }

            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data produksi berhasil disimpan',
                'kode_transaksi' => $kode_transaksi,
                'total_items' => count($request->items)
            ]);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data produksi'
            ], 400);
        }
    }

     public function list(Request $request)
    {
        $search = $request->input('search', '');
        $paginate = $request->input('paginate', 10);
        $tanggalDari = $request->input('tanggal_dari');
        $tanggalSampai = $request->input('tanggal_sampai');

        $transaksi = Transaksi::with('transaksiDetail')
            ->where('jenis_transaksi', 'produksi')
            ->where('status', 'aktif')
            ->orderBy('created_at', 'desc')
            ->when($search, function ($query, $search) {
                return $query->where('kode_transaksi', 'like', '%' . $search . '%');
            })
            ->when($tanggalDari && $tanggalSampai, function ($query) use ($tanggalDari, $tanggalSampai) {
                return $query->whereBetween('tanggal', [$tanggalDari, $tanggalSampai]);
            })
            ->paginate($paginate);

        return view('produksi.history', compact('transaksi'));
    }

    public function getDetail($id)
    {
        $transaksi = Transaksi::with('transaksi_detail')
            ->where('jenis_transaksi', 'produksi')->findOrFail($id);

        try {

            return response()->json([
                'status' => 'success',
                'data' => $transaksi->transaksi_detail,
                'transaksi' => $transaksi
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mendapatkan detail transaksi'
            ], 500);
        }
    }

    public function destroy(ConfirmTransactionDeletion $request, $id)
    {
        $transaksi = Transaksi::where('jenis_transaksi', 'produksi')->findOrFail($id);

        try {
            DB::transaction(function () use ($transaksi) {
                $barangIds = $transaksi->transaksiDetail()->pluck('barang_id')->unique();
                Barang::whereIn('id', $barangIds)->orderBy('id')->lockForUpdate()->get();

                $transaksi->status = 'dibatalkan';
                $transaksi->save();

                Stok_masuk::whereIn('transaksi_detail_id', $transaksi->transaksiDetail()->select('id'))->delete();
            });

            return redirect()->back()->with('success', 'Transaksi ' . $transaksi->kode_transaksi . ' berhasil dihapus');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Gagal menghapus transaksi');
        }
    }
}
