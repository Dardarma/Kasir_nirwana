<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\Transaksi_detail;
use App\Models\Stok_keluar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\sub_barang;
use App\Models\Customer;
use Illuminate\Validation\Rule;

class KasirController extends Controller
{
    public function index()
    {

        $customers = Customer::query()
            ->where('status', 'aktif')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('kasir', compact('customers'));
    }

    public function store(Request $request)
    {
        try {
            // Debug: Log data yang diterima
            Log::info('Request data received:', $request->all());

            $request->validate([
                'tanggal' => 'required|date',
                'customer_id' => [
                    'required',
                    Rule::exists('customers', 'id')->where('status', 'aktif'),
                ],
                'total' => 'required|numeric|min:0',
                'bayar' => 'required|numeric|min:0',
                'customer_bayar' => 'required|numeric|min:0',
                'items' => 'required|array',
                'metode_pembayaran' => 'required|in:tunai,transfer,qris',
                'items.*.barang_id' => 'required|exists:barang,id',
                'items.*.nama_barang' => 'required|string|max:255',
                'items.*.jumlah' => 'required|numeric|min:1',
                'items.*.satuan' => 'required|string|max:50',
                'items.*.harga' => 'required|numeric|min:0',
                'items.*.sub_total' => 'required|numeric|min:0',
                'items.*.sub_barang_id' => 'required|exists:sub_barang,id',
                'items.*.jumlah_gram' => 'required|numeric|min:0',
            ]);

            $customer = Customer::findOrFail($request->customer_id);

            $customerBayar = $request->customer_bayar;
            $total = $request->total;
            $statusPembayaran = ($customerBayar >= $total) ? 'lunas' : 'belum_lunas';

            if ($statusPembayaran === 'lunas') {
                $tanggal_pembayaran = Carbon::now()->format('Y-m-d');
            } else {
                $tanggal_pembayaran = null; 
            }

            $tanggal_now = Carbon::now()->format('Ymd');
            $jumlah_trasaksi_today = Transaksi::whereDate('created_at', Carbon::today())->count();
            $kode_transaksi = 'KAS' . $tanggal_now . str_pad($jumlah_trasaksi_today + 1, 4, '0', STR_PAD_LEFT);

            // Validasi stok sebelum menyimpan
            $kebutuhanStok = [];
            foreach ($request->items as $index => $item) {
                $barang = Barang::find($item['barang_id']);
                if (!$barang) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Barang dengan ID ' . $item['barang_id'] . ' tidak ditemukan'
                    ], 400);
                }

                $subBarang = sub_barang::where('id', $item['sub_barang_id'])
                    ->where('barang_id', $item['barang_id'])
                    ->where('customer_id', $customer->id)
                    ->first();

                if (!$subBarang) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Konversi satuan untuk barang {$barang->nama_barang} tidak valid."
                    ], 400);
                }

                $jumlahGram = $item['jumlah'] * $subBarang->gramasi;
                $kebutuhanStok[$index] = $jumlahGram;

                // Hitung stok saat ini
                $stok_masuk = DB::table('stok_masuk')->where('barang_id', $item['barang_id'])->sum('qty_masuk');
                $stok_keluar = DB::table('stok_keluar')->where('barang_id', $item['barang_id'])->sum('qty_keluar');
                $stok_tersedia = $stok_masuk - $stok_keluar;

                if ($stok_tersedia < $jumlahGram) {
                    $stokDalamSatuanJual = $stok_tersedia / $subBarang->gramasi;
                    if (strtolower($subBarang->satuan) === 'pcs') {
                        $stokDalamSatuanJual = floor($stokDalamSatuanJual);
                    }

                    return response()->json([
                        'status' => 'error',
                        'message' => "Stok tidak mencukupi untuk barang {$barang->nama_barang}. Stok tersedia: {$stokDalamSatuanJual} {$subBarang->satuan}, diminta: {$item['jumlah']} {$subBarang->satuan}"
                    ], 400);
                }
            }

            $transaksi = Transaksi::create([
                'tanggal' => $request->tanggal,
                'customer_id' => $customer->id,
                'customer' => $customer->name,
                'status_pembayaran' => $statusPembayaran,
                'total_bayar' => $request->total,
                'customer_bayar' => $request->customer_bayar,
                'kode_transaksi' => $kode_transaksi,
                'jenis_transaksi' => 'penjualan',
                'tanggal_pembayaran' => $tanggal_pembayaran,
                'status' => 'aktif',
                'metode_pembayaran' => $request->metode_pembayaran,
            ]);

            foreach ($request->items as $index => $item) {
                $jumlahGram = $kebutuhanStok[$index];
                $transaksi_detail = Transaksi_detail::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                    'satuan' => $item['satuan'],
                    'nama_barang' => $item['nama_barang'],
                    'harga_satuan' => $item['harga'],
                    'subtotal' => $item['sub_total'],
                    'kategori_barang' => 'jadi',
                    'sub_barang_id' => $item['sub_barang_id'],
                    'jumlah_gram' => $jumlahGram,
                ]);

                Stok_keluar::create([
                    'barang_id' => $item['barang_id'],
                    'transaksi_detail_id' => $transaksi_detail->id,
                    'qty_keluar' => $jumlahGram,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data kasir berhasil disimpan',
                'kode_transaksi' => $kode_transaksi,
                'status_pembayaran' => $statusPembayaran,
                'total_items' => count($request->items)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data kasir: ' . $e->getMessage()
            ], 400);
        }
    }

    public function list(Request $request)
    {
        $search = $request->input('table_search', '');
        $paginate = $request->input('paginate', 10);
        $tanggalDari = $request->input('tanggal_dari');
        $tanggalSampai = $request->input('tanggal_sampai');

        $transaksi = Transaksi::with(['transaksiDetail', 'customerData'])
            ->where('jenis_transaksi', 'penjualan')
            ->where('status', 'aktif')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('kode_transaksi', 'like', '%' . $search . '%')
                        ->orWhere('customer', 'like', '%' . $search . '%')
                        ->orWhereHas('customerData', function ($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($tanggalDari && $tanggalSampai, function ($query) use ($tanggalDari, $tanggalSampai) {
                return $query->whereBetween('tanggal', [$tanggalDari, $tanggalSampai]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($paginate);

        return view('list.kasir_list', compact('transaksi'));
    }


    public function getDetail($id)
    {
        try {
            $transaksi = Transaksi::with(['transaksiDetail', 'customerData'])->findOrFail($id);
            $transaksi->customer = $transaksi->customerData?->name ?? $transaksi->customer;
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

    public function getStokRealtime($sub_barang_id)
    {
        try {
            if ($sub_barang_id) {

                $subBarang = sub_barang::select('barang_id', 'gramasi', 'satuan')
                    ->where('id', $sub_barang_id)
                    ->firstOrFail();
                $gramasi = $subBarang->gramasi;
                $barang_id = $subBarang->barang_id;
                $stok = get_stok_by_id($barang_id, $gramasi);

                if (strtolower($subBarang->satuan) === 'pcs') {
                    $stok = floor($stok);
                }

                return response()->json([
                    'status' => 'success',
                    'barang_id' => $barang_id,
                    'stok' => $stok,
                    'satuan' => $subBarang->satuan
                ]);
            } else {
                // Get semua stok
                $stok = get_stok();
                return response()->json([
                    'status' => 'success',
                    'data' => $stok
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data stok: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBarangByCustomer($customer_id)
    {
        try {
            $barang = sub_barang::with('barang')
                ->where('customer_id', $customer_id)
                ->whereHas('customer', function ($query) {
                    $query->where('status', 'aktif');
                })
                ->whereHas('barang', function ($query) {
                    $query->where('status', 'aktif');
                })
                ->where('status', 'aktif')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $barang,
                'message' => 'Data barang berhasil dimuat'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data barang: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editStatusPembayaran(Request $request, $id)
    {
        try {
            $transaksi = Transaksi::findOrFail($id);

            if ($transaksi->status_pembayaran === 'lunas') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pembayaran sudah lunas dan tidak dapat diubah.'
                ], 422);
            }

            $request->validate([
                'customer_bayar' => 'required|numeric|min:0'
            ]);

            $transaksi->customer_bayar = $request->input('customer_bayar');



            // Update status berdasarkan logic pembayaran
            if ($transaksi->customer_bayar >= $transaksi->total_bayar) {
                $transaksi->status_pembayaran = 'lunas';
                $transaksi->tanggal_pembayaran = Carbon::now()->format('Y-m-d');
            } else {
                $transaksi->status_pembayaran = 'belum_lunas';
            }

            $transaksi->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil diupdate',
                'data' => [
                    'customer_bayar' => $transaksi->customer_bayar,
                    'status_pembayaran' => $transaksi->status_pembayaran,
                    'kembalian' => max(0, $transaksi->customer_bayar - $transaksi->total_bayar)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate status pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $transaksi = Transaksi::with('transaksiDetail')->findOrFail($id);

            // Soft delete dengan mengubah status
            $transaksi->status = 'dibatalkan';
            $transaksi->save();

            // Hapus stok keluar yang terkait untuk mengembalikan stok
            foreach ($transaksi->transaksiDetail as $detail) {
                Stok_keluar::where('transaksi_detail_id', $detail->id)->delete();
            }

            return redirect()->back()->with('success', 'Transaksi ' . $transaksi->kode_transaksi . ' berhasil dihapus dan stok dikembalikan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
