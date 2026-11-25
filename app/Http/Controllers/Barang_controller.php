<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Satuan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        ->paginate($paginate)
        ->withQueryString();

        return view('barang.index', [
            'barang' => $barang,
            'satuanList' => Satuan::orderBy('nama')->pluck('nama'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateBarang($request);

        try {
            $barang = DB::transaction(function () use ($validated) {
                $barang = Barang::create($validated + ['status' => 'aktif']);

                return $barang;
            });
            Log::info('Barang created:', $barang->toArray());
            return redirect('/barang')->with('success', 'Barang berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error creating barang:', ['error' => $e->getMessage()]);
            return redirect('/barang')->with('error', 'Gagal menambahkan barang');
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateBarang($request);

        $barang = Barang::findOrFail($id);
        if (($barang->kategori !== $validated['kategori'] || $barang->satuan !== $validated['satuan'])
            && \App\Models\Transaksi_detail::where('barang_id', $barang->id)->exists()) {
            return back()->withErrors(['satuan' => 'Kategori dan satuan barang yang sudah memiliki transaksi tidak dapat diubah.']);
        }

        try {
            DB::transaction(function () use ($barang, $validated) {
                $barang = Barang::lockForUpdate()->findOrFail($barang->id);
                if ($barang->isNonStok() !== ($validated['sub_kategori'] === Barang::NON_STOK)
                    && ($barang->sub_barang()->exists() || $barang->transaksi_detail()->exists())) {
                    throw ValidationException::withMessages([
                        'sub_kategori' => 'Barang yang sudah memiliki sub barang atau transaksi tidak dapat diubah antara stok dan nonstok.',
                    ]);
                }
                $barang->update($validated);
            });
            Log::info('Barang updated:', $barang->toArray());
            return redirect('/barang')->with('success', 'Barang berhasil diperbarui.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating barang:', ['error' => $e->getMessage()]);
            return redirect('/barang')->with('error', 'Gagal memperbarui barang');
        }
    }

    private function validateBarang(Request $request): array
    {
        $jadi = $request->input('kategori') === 'jadi';
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'required|exists:satuan,nama',
            'kategori' => 'required|in:jadi,produksi',
            'sub_kategori' => [
                $jadi ? 'sometimes' : 'required',
                Rule::in($jadi ? ['barang_jadi', Barang::NON_STOK] : ['bahan_baku', 'penolong/alat', 'produk_jadi']),
            ],
        ]);

        if ($validated['kategori'] === 'jadi') {
            $validated['sub_kategori'] ??= 'barang_jadi';
        }
        return $validated;
    }

    public function destroy($id)
    {
        try{
            DB::transaction(function () use ($id) {
                $barang = Barang::lockForUpdate()->findOrFail($id);
                $barang->update(['status' => 'nonaktif']);
            });
            return redirect('/barang')->with('success', 'Barang berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting barang:', ['error' => $e->getMessage()]);
            return redirect('/barang')->with('error', 'Gagal menghapus barang');
        }
    }
}
