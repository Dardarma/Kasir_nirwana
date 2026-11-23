<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\sub_barang;
use App\Models\Barang;
use App\Models\Customer;
use App\Models\Satuan;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;


class ControllerSub_barang extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $paginate = $request->input('paginate', 10);
        $customerId = $request->input('customer');

        $produk = sub_barang::where('status', 'aktif')
            ->when($search, function ($query, $search) {
                return $query->where('sub_nama', 'like', '%' . $search . '%');
            })
            ->when($customerId, function ($query, $customerId) {
                return $query->where('customer_id', $customerId);
            })
            ->with(['barang', 'customer'])
            ->paginate($paginate)
            ->withQueryString();

            // dd($produk);
        $barang = Barang::select('id', 'nama_barang', 'satuan', 'sub_kategori')
            ->where('status', 'aktif')
            ->whereIn('sub_kategori', ['barang_jadi', 'produk_jadi', Barang::NON_STOK])
            ->get();

        $customers = Customer::query()
            ->where('status', 'aktif')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $conversions = Satuan::konversiStok();
        return view('sub-barang.index', compact('produk', 'barang', 'customers', 'conversions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barang' => 'required|exists:barang,id',
            'sub_nama' => 'required|string|max:255',
            'customer' => ['required', Rule::exists('customers', 'id')->where('status', 'aktif')],
            'satuan' => 'required|string|exists:satuan,nama',
            'harga' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
        ]);

        $attributes = $this->saleAttributes($request, $request->barang);

        try {
            sub_barang::create([
                'barang_id' => $request->barang,
                'sub_nama' => $request->sub_nama,
                'customer_id' => $request->customer,
                'satuan' => $request->satuan,
                'status' => 'aktif'
            ] + $attributes);

            return redirect()->route('sub_barang.index')->with('success', 'Sub barang berhasil ditambahkan');
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('sub_barang.index')->with('error', 'Gagal menambahkan sub barang');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_barang' => 'required|exists:barang,id',
            'sub_nama' => 'required|string|max:255',
            'customer' => ['required', Rule::exists('customers', 'id')->where('status', 'aktif')],
            'satuan' => 'required|string|exists:satuan,nama',
            'harga' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
        ]);

        $attributes = $this->saleAttributes($request, $request->nama_barang);

        try {
            $subBarang = sub_barang::findOrFail($id);
            $subBarang->update([
                'barang_id' => $request->nama_barang,
                'sub_nama' => $request->sub_nama,
                'customer_id' => $request->customer,
                'satuan' => $request->satuan,
            ] + $attributes);

            return redirect()->route('sub_barang.index')->with('success', 'Sub barang berhasil diupdate');
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('sub_barang.index')->with('error', 'Gagal mengupdate sub barang');
        }
    }

    public function destroy($id)
    {
        try {
            $subBarang = sub_barang::findOrFail($id);
            $subBarang->update(['status' => 'nonaktif']);

            return redirect()->route('sub_barang.index')->with('success', 'Sub barang berhasil dihapus');
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('sub_barang.index')->with('error', 'Gagal menghapus sub barang');
        }
    }

    private function saleAttributes(Request $request, int $barangId): array
    {
        $barang = Barang::findOrFail($barangId);
        [$dasar] = Satuan::dasarStok($request->satuan);
        if ($dasar !== Satuan::dasarStok($barang->satuan)[0]) {
            throw ValidationException::withMessages(['satuan' => 'Pilih satuan yang memiliki satuan dasar sama dengan barang.']);
        }

        if (! $barang->isNonStok()) {
            $request->validate(['harga' => 'required']);
        }

        return [
            'satuan_terkecil' => $dasar,
            'harga' => $barang->isNonStok() ? null : $request->harga,
        ];
    }
}
