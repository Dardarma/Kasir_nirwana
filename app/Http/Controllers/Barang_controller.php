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

        return view('barang.index', ['barang' => $barang, 'satuanList' => \App\Models\Satuan::orderBy('nama')->pluck('nama')]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'required|exists:satuan,nama',
            'kategori' => 'required|in:jadi,produksi',
            'sub_kategori' => 'nullable|string|max:50',
            'harga' => 'required|numeric|min:0',
        ]);

        if ($validated['kategori'] === 'jadi') {
            $validated['sub_kategori'] = 'barang_jadi';
        }

        try {
            $barang = Barang::create($validated);
            $barang->status = 'aktif'; 
            $barang->save();
            Log::info('Barang created:', $barang->toArray());
            return redirect('/barang')->with('success', 'Barang berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error creating barang:', ['error' => $e->getMessage()]);
            return redirect('/barang')->with('error', 'Gagal menambahkan barang');
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'required|exists:satuan,nama',
            'kategori' => 'required|in:jadi,produksi',
            'sub_kategori' => 'nullable|string|max:50',
            'harga' => 'required|numeric|min:0',
        ]);

        if ($validated['kategori'] === 'jadi') {
            $validated['sub_kategori'] = 'barang_jadi';
        }

        $barang = Barang::findOrFail($id);
        if (($barang->kategori !== $validated['kategori'] || $barang->satuan !== $validated['satuan'])
            && \App\Models\Transaksi_detail::where('barang_id', $barang->id)->exists()) {
            return back()->withErrors(['satuan' => 'Kategori dan satuan barang yang sudah memiliki transaksi tidak dapat diubah.']);
        }

        try {
            $barang->update($validated);
            Log::info('Barang updated:', $barang->toArray());
            return redirect('/barang')->with('success', 'Barang berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error updating barang:', ['error' => $e->getMessage()]);
            return redirect('/barang')->with('error', 'Gagal memperbarui barang');
        }
    }

    public function destroy($id)
    {
        try{
            $barang = Barang::findOrFail($id);
            $barang->status = 'nonaktif';
            $barang->save();
            return redirect('/barang')->with('success', 'Barang berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting barang:', ['error' => $e->getMessage()]);
            return redirect('/barang')->with('error', 'Gagal menghapus barang');
        }
    }
}
