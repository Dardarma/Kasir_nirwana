<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $permissions = Permission::query()
            ->withCount('roles')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('slug')
            ->paginate(15)
            ->withQueryString();

        $systemPermissions = array_keys(config('access.permissions', []));

        return view('permissions.index', compact('permissions', 'systemPermissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        Permission::create($this->validatePermission($request));

        return redirect()->route('permissions.index')->with('success', 'Permission berhasil ditambahkan.');
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $permission->update($validated);

        return redirect()->route('permissions.index')->with('success', 'Keterangan permission berhasil diperbarui.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        if (array_key_exists($permission->slug, config('access.permissions', []))) {
            return back()->with('error', 'Permission bawaan sistem tidak dapat dihapus.');
        }

        if ($permission->roles()->exists()) {
            return back()->with('error', 'Permission masih digunakan oleh role dan tidak dapat dihapus.');
        }

        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission berhasil dihapus.');
    }

    private function validatePermission(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9]+(?:[.-][a-z0-9]+)*$/',
                Rule::unique('permissions', 'slug'),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'slug.regex' => 'Kode permission harus memakai format modul.aksi, misalnya laporan.view.',
        ]);
    }
}
