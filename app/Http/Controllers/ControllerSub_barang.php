<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\sub_barang;
use App\Models\barang;
use App\Models\Customer;
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
            ->paginate($paginate);

            // dd($produk);
        $barang = barang::select('id', 'nama_barang')
            ->where('status', 'aktif')
            ->whereIn('sub_kategori', ['barang_jadi', 'produk_jadi'])
            ->get();

        $customers = Customer::query()
            ->where('status', 'aktif')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('list.Sub_barang_list', compact('produk', 'barang', 'customers'));
    }

    public function create(Request $request)
    {
       $request->validate([
            'barang' => 'required|exists:barang,id',
            'sub_nama' => 'required|string|max:255',
            'customer' => ['required', Rule::exists('customers', 'id')->where('status', 'aktif')],
            'satuan' => 'required|in:kg,gram,pcs',
            'gramasi' => 'required|integer|min:1',
            'harga' => 'required|integer|min:0',
       ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'barang' => 'required|exists:barang,id',
            'sub_nama' => 'required|string|max:255',
            'customer' => ['required', Rule::exists('customers', 'id')->where('status', 'aktif')],
            'satuan' => 'required|in:kg,gram,pcs',
            'gramasi' => 'required|integer|min:1',
            'harga' => 'required|integer|min:0',
        ]);

        try {
            sub_barang::create([
                'barang_id' => $request->barang,
                'sub_nama' => $request->sub_nama,
                'customer_id' => $request->customer,
                'satuan' => $request->satuan,
                'gramasi' => $request->gramasi,
                'harga' => $request->harga,
                'status' => 'aktif'
            ]);

            return redirect()->route('sub_barang.index')->with('success', 'Sub barang berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('sub_barang.index')->with('error', 'Gagal menambahkan sub barang: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_barang' => 'required|exists:barang,id',
            'sub_nama' => 'required|string|max:255',
            'customer' => ['required', Rule::exists('customers', 'id')->where('status', 'aktif')],
            'satuan' => 'required|in:kg,gram,pcs',
            'gramasi' => 'required|integer|min:1',
            'harga' => 'required|integer|min:0',
        ]);

        try {
            $subBarang = sub_barang::findOrFail($id);
            $subBarang->update([
                'barang_id' => $request->nama_barang,
                'sub_nama' => $request->sub_nama,
                'customer_id' => $request->customer,
                'satuan' => $request->satuan,
                'gramasi' => $request->gramasi,
                'harga' => $request->harga,
            ]);

            return redirect()->route('sub_barang.index')->with('success', 'Sub barang berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->route('sub_barang.index')->with('error', 'Gagal mengupdate sub barang: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $subBarang = sub_barang::findOrFail($id);
            $subBarang->update(['status' => 'nonaktif']);

            return redirect()->route('sub_barang.index')->with('success', 'Sub barang berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('sub_barang.index')->with('error', 'Gagal menghapus sub barang: ' . $e->getMessage());
        }
    }
}
