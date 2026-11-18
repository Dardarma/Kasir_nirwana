<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmTransactionDeletion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\Transaksi_detail;
use App\Models\Stok_keluar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\sub_barang;
use App\Models\Customer;
use App\Models\Satuan;
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

        return view('kasir.index', compact('customers'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'tanggal' => 'required|date',
                'customer_id' => [
                    'required',
                    Rule::exists('customers', 'id')->where('status', 'aktif'),
                ],
                'bayar' => 'required|numeric|min:0',
                'customer_bayar' => 'required|numeric|min:0',
                'items' => 'required|array',
                'metode_pembayaran' => 'required|in:tunai,transfer,qris',
                'items.*.barang_id' => 'required|exists:barang,id',
                'items.*.nama_barang' => 'required|string|max:255',
                'items.*.jumlah' => 'required|numeric|min:0.01|decimal:0,2|max:999999999',
                'items.*.sub_barang_id' => 'required|exists:sub_barang,id',
            ], ['items.*.jumlah.decimal' => 'Jumlah maksimal dua angka desimal. Pilih satuan yang lebih kecil bila diperlukan.', 'items.*.jumlah.min' => 'Jumlah minimal 0,01. Pilih satuan yang lebih kecil bila diperlukan.']);

            $customer = Customer::findOrFail($request->customer_id);

            $customerBayar = $request->customer_bayar;
            $total = 0;

            $kode_transaksi = Transaksi::generateCode('KAS');

            return DB::transaction(function () use ($request, $customer, $kode_transaksi, $customerBayar, $total) {
                $barangIds = collect($request->items)->pluck('barang_id')->unique();
                $barangTerkunci = Barang::whereIn('id', $barangIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $stokTerpakai = [];
                $conversions = Satuan::konversiStok();

                // Validasi stok sebelum menyimpan
                $items = $request->items;
                foreach ($items as $index => $item) {
                    $barang = $barangTerkunci->get($item['barang_id']);
                    if (!$barang) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Barang dengan ID ' . $item['barang_id'] . ' tidak ditemukan'
                        ], 400);
                    }

                    $subBarang = sub_barang::where('id', $item['sub_barang_id'])
                        ->where('barang_id', $item['barang_id'])
                        ->where('customer_id', $customer->id)
                        ->where('status', 'aktif')
                        ->first();

                    if (! $subBarang || $barang->status !== 'aktif') {
                        throw ValidationException::withMessages([
                            "items.{$index}.sub_barang_id" => 'Sub barang tidak tersedia untuk pelanggan ini.',
                        ]);
                    }

                    if ($barang->isNonStok()) {
                        $priceKey = "items.{$index}.harga";
                        $request->validate(
                            [$priceKey => 'required|integer|min:0|max:2147483647'],
                            [],
                            [$priceKey => "harga satuan {$subBarang->sub_nama}"],
                        );
                        $harga = (int) $request->input($priceKey);
                        $subtotal = (int) round($item['jumlah'] * $harga);
                        $items[$index] = array_replace($item, [
                            'satuan' => $subBarang->satuan,
                            'harga' => $harga,
                            'sub_total' => $subtotal,
                            'jumlah_gram' => 0,
                            'faktor_satuan' => null,
                            'satuan_stok' => null,
                        ]);
                        $total += $subtotal;

                        continue;
                    }

                    $conversion = $conversions[$subBarang->satuan] ?? null;
                    $base = $conversions[$barang->satuan]['dasar'] ?? null;
                    $factor = $conversion && $conversion['dasar'] === $base ? $conversion['faktor'] : 0;
                    if ($factor <= 0 || $subBarang->harga === null) {
                        return response()->json([
                            'status' => 'error',
                            'message' => "Konversi satuan untuk barang {$barang->nama_barang} tidak valid."
                        ], 400);
                    }

                    $jumlahGram = round($item['jumlah'] * $factor, 2);
                    if ($jumlahGram <= 0 || $jumlahGram >= 1e14) {
                        return response()->json(['status' => 'error', 'message' => 'Jumlah penjualan tidak valid.'], 422);
                    }
                    $items[$index]['satuan'] = $subBarang->satuan;
                    $items[$index]['faktor_satuan'] = $factor;
                    $items[$index]['satuan_stok'] = $base;
                    $items[$index]['jumlah_gram'] = $jumlahGram;
                    $items[$index]['harga'] = $subBarang->harga;
                    $items[$index]['sub_total'] = $item['jumlah'] * $subBarang->harga;
                    $total += $items[$index]['sub_total'];

                    // Hitung stok saat ini
                    $stok_masuk = DB::table('stok_masuk')->where('barang_id', $item['barang_id'])->sum('qty_masuk');
                    $stok_keluar = DB::table('stok_keluar')->where('barang_id', $item['barang_id'])->sum('qty_keluar');
                    $stok_tersedia = round($stok_masuk - $stok_keluar - ($stokTerpakai[$barang->id] ?? 0), 2);

                    if ($stok_tersedia < $jumlahGram) {
                        $stokDalamSatuanJual = format_jumlah($stok_tersedia / $factor);
                        $jumlahDiminta = format_jumlah($item['jumlah']);

                        return response()->json([
                            'status' => 'error',
                            'message' => "Stok tidak mencukupi untuk barang {$barang->nama_barang}. Stok tersedia: {$stokDalamSatuanJual} {$subBarang->satuan}, diminta: {$jumlahDiminta} {$subBarang->satuan}"
                        ], 400);
                    }
                    $stokTerpakai[$barang->id] = ($stokTerpakai[$barang->id] ?? 0) + $jumlahGram;
                }

                $statusPembayaran = $customerBayar >= $total ? 'lunas' : 'belum_lunas';
                $tanggal_pembayaran = $statusPembayaran === 'lunas' ? Carbon::now()->toDateString() : null;

                $transaksi = Transaksi::create([
                    'tanggal' => $request->tanggal,
                    'customer_id' => $customer->id,
                    'customer' => $customer->name,
                    'status_pembayaran' => $statusPembayaran,
                    'total_bayar' => $total,
                    'customer_bayar' => $request->customer_bayar,
                    'kode_transaksi' => $kode_transaksi,
                    'jenis_transaksi' => 'penjualan',
                    'tanggal_pembayaran' => $tanggal_pembayaran,
                    'status' => 'aktif',
                    'metode_pembayaran' => $request->metode_pembayaran,
                ]);

                foreach ($items as $item) {
                    $jumlahGram = $item['jumlah_gram'];
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
                        'faktor_satuan' => $item['faktor_satuan'],
                        'satuan_stok' => $item['satuan_stok'],
                    ]);

                    if ($jumlahGram > 0) {
                        Stok_keluar::create([
                            'barang_id' => $item['barang_id'],
                            'transaksi_detail_id' => $transaksi_detail->id,
                            'qty_keluar' => $jumlahGram,
                        ]);
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Data kasir berhasil disimpan',
                    'kode_transaksi' => $kode_transaksi,
                    'status_pembayaran' => $statusPembayaran,
                    'total_items' => count($request->items)
                ]);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data kasir'
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

        return view('kasir.history', compact('transaksi'));
    }


    public function getDetail($id)
    {
        $transaksi = Transaksi::with(['transaksiDetail', 'customerData'])
            ->where('jenis_transaksi', 'penjualan')->findOrFail($id);

        try {
            $transaksi->customer = $transaksi->customerData?->name ?? $transaksi->customer;
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

    public function getStokRealtime($sub_barang_id)
    {
        try {
            if ($sub_barang_id) {

                $subBarang = sub_barang::with('barang')->select('barang_id', 'satuan')
                    ->where('id', $sub_barang_id)
                    ->firstOrFail();
                if ($subBarang->barang->isNonStok()) {
                    return response()->json([
                        'status' => 'success',
                        'barang_id' => $subBarang->barang_id,
                        'stok' => null,
                        'non_stok' => true,
                        'satuan' => $subBarang->satuan,
                    ]);
                }

                [$base, $factor] = Satuan::dasarStok($subBarang->satuan);
                if ($base !== Satuan::dasarStok($subBarang->barang->satuan)[0] || $factor <= 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Konversi satuan barang tidak valid.',
                    ], 422);
                }

                $barang_id = $subBarang->barang_id;
                $stok = get_stok_by_id($barang_id, $factor);

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
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data stok'
            ], 500);
        }
    }

    public function getBarangByCustomer($customer_id)
    {
        try {
            $conversions = Satuan::konversiStok();
            $barang = sub_barang::with('barang')
                ->where('customer_id', $customer_id)
                ->whereHas('customer', function ($query) {
                    $query->where('status', 'aktif');
                })
                ->whereHas('barang', function ($query) {
                    $query->where('status', 'aktif');
                })
                ->where('status', 'aktif')
                ->get()
                ->filter(fn ($item) => $item->barang->isNonStok() || (
                    isset($conversions[$item->satuan], $conversions[$item->barang->satuan])
                    && $conversions[$item->satuan]['dasar'] === $conversions[$item->barang->satuan]['dasar']
                ))
                ->values();

            foreach ($barang as $item) {
                $item->non_stok = $item->barang->isNonStok();
                $item->faktor_satuan = $item->non_stok ? null : $conversions[$item->satuan]['faktor'];
            }

            return response()->json([
                'status' => 'success',
                'data' => $barang,
                'message' => 'Data barang berhasil dimuat'
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data barang'
            ], 500);
        }
    }

    public function editStatusPembayaran(Request $request, $id)
    {
        $transaksi = Transaksi::where('jenis_transaksi', 'penjualan')->findOrFail($id);

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
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate status pembayaran'
            ], 500);
        }
    }

    public function destroy(ConfirmTransactionDeletion $request, $id)
    {
        $transaksi = Transaksi::where('jenis_transaksi', 'penjualan')->findOrFail($id);

        try {

            DB::transaction(function () use ($transaksi) {
                $transaksi = Transaksi::lockForUpdate()->findOrFail($transaksi->id);
                $barangIds = $transaksi->transaksiDetail()->lockForUpdate()->pluck('barang_id');
                Barang::whereIn('id', $barangIds)->orderBy('id')->lockForUpdate()->get();

                Stok_keluar::whereIn('transaksi_detail_id', $transaksi->transaksiDetail()->select('id'))->delete();
                $transaksi->update(['status' => 'dibatalkan']);
            });

            return redirect()->back()->with('success', 'Transaksi ' . $transaksi->kode_transaksi . ' berhasil dihapus dan stok dikembalikan');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Gagal menghapus transaksi');
        }
    }
}
