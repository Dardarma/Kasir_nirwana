<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use Illuminate\Support\Facades\Log;

class Barang_controller extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $paginate = $request->input('paginate',10);
        $kategori = $request->input('Kategori', 'semua');

        $barang =  Barang::when($search, function ($query, $search) {
            return $query->where('nama_barang', 'like', '%' . $search . '%');
        })->when($kategori != 'semua', function ($query) use ($kategori) {
            return $query->where('kategori', $kategori);
        })
        ->where('status','aktif')
        ->paginate($paginate);

        return view('list.Barang_list', compact('barang'));
    }

    public function store(Request $request)
    {
        // Debug: Log data yang diterima
        Log::info('Data received:', $request->all());
        
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'kategori' => 'required|string|max:50',
            'sub_kategori' => 'nullable|string|max:50',
            'harga' => 'required|numeric|min:0',
        ]);

        try {
            $barang = Barang::create($request->all());
            $barang->status = 'aktif'; 
            $barang->save();
            Log::info('Barang created:', $barang->toArray());
            return redirect('/barang')->with('success', 'Barang berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error creating barang:', ['error' => $e->getMessage()]);
            return redirect('/barang')->with('error', 'Gagal menambahkan barang: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'kategori' => 'required|string|max:50',
            'sub_kategori' => 'nullable|string|max:50',
            'harga' => 'required|numeric|min:0',
        ]);

        try {
            $barang = Barang::findOrFail($id);
            $barang->update($request->all());
            Log::info('Barang updated:', $barang->toArray());
            return redirect('/barang')->with('success', 'Barang berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error updating barang:', ['error' => $e->getMessage()]);
            return redirect('/barang')->with('error', 'Gagal memperbarui barang: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try{
            $barang = Barang::findOrFail($id);
            $barang->status = 'non_aktif';
            $barang->save();
            return redirect('/barang')->with('success', 'Barang berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting barang:', ['error' => $e->getMessage()]);
            return redirect('/barang')->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        }
    }
}
