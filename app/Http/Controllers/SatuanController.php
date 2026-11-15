<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SatuanController extends Controller
{
    public function index()
    {
        return view('satuan.index', [
            'units' => Satuan::with('parent')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->save($request, new Satuan());

        return back()->with('success', 'Satuan ditambahkan.');
    }

    public function update(Request $request, Satuan $satuan)
    {
        $this->save($request, $satuan);

        return back()->with('success', 'Satuan diperbarui.');
    }

    public function destroy(Satuan $satuan)
    {
        DB::transaction(function () use ($satuan) {
            $unit = Satuan::lockForUpdate()->findOrFail($satuan->id);
            if ($unit->children()->exists() || $this->isUsed($unit)) {
                throw ValidationException::withMessages(['nama' => 'Satuan masih dipakai.']);
            }
            $unit->delete();
        });

        return back()->with('success', 'Satuan dihapus.');
    }

    private function save(Request $request, Satuan $unit): void
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:50', Rule::unique('satuan')->ignore($unit->id)],
            'is_satuan_terkecil' => ['sometimes', 'boolean'],
            'parent_id' => ['nullable', 'integer'],
            'faktor' => ['nullable', 'required_with:parent_id', 'numeric', 'min:0.000001', 'max:999999999'],
        ]);
        $data['is_satuan_terkecil'] = $request->boolean('is_satuan_terkecil');
        $data['parent_id'] = $data['parent_id'] ?? null;
        $data['faktor'] = $data['parent_id'] ? $data['faktor'] : null;

        DB::transaction(function () use ($unit, $data) {
            $locked = Satuan::whereIn('id', array_filter([$unit->id, $data['parent_id']]))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $current = $unit->exists ? $locked->get($unit->id) : $unit;
            abort_if($current === null, 404);
            $parent = $locked->get($data['parent_id']);
            if ($data['parent_id'] && (! $parent?->is_satuan_terkecil || $parent->is($current) || $data['is_satuan_terkecil'])) {
                throw ValidationException::withMessages(['parent_id' => 'Pilih satuan terkecil lain sebagai induk.']);
            }
            if ($current->exists && (
                (! $data['is_satuan_terkecil'] && $current->children()->exists())
                || ($current->nama !== $data['nama'] && ($this->isUsed($current) || $current->children()->exists()))
                || ($this->isUsed($current) && (
                    $current->parent_id != $data['parent_id']
                    || $current->faktor != $data['faktor']
                    || ($current->is_satuan_terkecil && ! $data['is_satuan_terkecil'])
                ))
            )) {
                throw ValidationException::withMessages(['nama' => 'Satuan masih dipakai.']);
            }
            $current->fill($data)->save();
        });
    }

    private function isUsed(Satuan $unit): bool
    {
        return DB::table('barang')->where('satuan', $unit->nama)->exists()
            || DB::table('sub_barang')->where('satuan', $unit->nama)->orWhere('satuan_terkecil', $unit->nama)->exists();
    }
}
