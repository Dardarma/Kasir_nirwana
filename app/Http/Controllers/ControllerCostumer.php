<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 

class ControllerCostumer extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $paginate = $request->input('paginate', 10);

        $costumers = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', '%' . $search . '%');
        })
        ->where('status', 'aktif')
        ->paginate($paginate);

        return view('list.Costumer', compact('costumers'));
    }

    public function store(Request $request)
    {
        try{
            $user = new User();
            $request->validate([
                'name' => 'required|string|max:255'
            ]);
    
            $user->name = $request->input('name');
            $user->status = 'aktif';
            $user->save();
    
            return redirect()->route('costumer.index')->with('success', 'Costumer berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->route('costumer.index')->with('error', 'Terjadi kesalahan saat menambahkan costumer.');
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $request->validate([
                'name' => 'required|string|max:255'
            ]);
    
            $costumer = User::findOrFail($id);
            $costumer->update($request->all());
    
            return redirect()->route('costumer.index')->with('success', 'Costumer berhasil diperbarui.');
        }catch (\Exception $e) {
            return redirect()->route('costumer.index')->with('error', 'Terjadi kesalahan saat memperbarui costumer: ' . $e->getMessage());
        }
    }

    public function destroy($id){
        try{
            $costumer = User::findOrFail($id);
            $costumer->status = 'non_aktif';
            $costumer->save();

            return redirect()->route('costumer.index')->with('success', 'Costumer berhasil dihapus.');
        }catch (\Exception $e) {
            return redirect()->route('costumer.index')->with('error', 'Terjadi kesalahan saat menghapus costumer: ' . $e->getMessage());
        }
    }
}
