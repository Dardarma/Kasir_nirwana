<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\sub_barang;
use App\Models\barang;
use App\Models\user;


class ControllerSub_barang extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $paginate = $request->input('paginate', 10);
        $costumer = $request->input('costumer');

        $produk = sub_barang::where('status', 'aktif')
            ->when($search, function ($query, $search) {
                return $query->where('nama_sub_barang', 'like', '%' . $search . '%');
            })
            ->when($costumer, function ($query, $costumer) {
                return $query->where('costumer_id', $costumer);
            })
            ->with('barang')
            ->with('user')
            ->paginate($paginate);

            // dd($produk);
        $barang = barang::select('id', 'nama_barang')
            ->where('status', 'aktif')
            ->where('sub_kategori', ['barang_jadi', 'produk_jadi'])
            ->get();

        $costumer = user::select('id', 'name')->get();

        return view('list.Sub_barang_list', compact('produk', 'barang', 'costumer'));
    }

    public function create(Request $request)
    {
       $request->validate([
            'barang' => 'required|exists:barang,id',
            'sub_nama' => 'required|string|max:255',
            'costumer' => 'required|exists:users,id',
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
            'costumer' => 'required|exists:users,id',
            'satuan' => 'required|in:kg,gram,pcs',
            'gramasi' => 'required|integer|min:1',
            'harga' => 'required|integer|min:0',
        ]);

        try {
            sub_barang::create([
                'barang_id' => $request->barang,
                'sub_nama' => $request->sub_nama,
                'user_id' => $request->costumer,
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
            'costumer' => 'required|exists:users,id',
            'satuan' => 'required|in:kg,gram,pcs',
            'gramasi' => 'required|integer|min:1',
            'harga' => 'required|integer|min:0',
        ]);

        try {
            $subBarang = sub_barang::findOrFail($id);
            $subBarang->update([
                'barang_id' => $request->nama_barang,
                'sub_nama' => $request->sub_nama,
                'user_id' => $request->costumer,
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
