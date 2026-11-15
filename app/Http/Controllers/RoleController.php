<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $roles = Role::query()
            ->with('permissions')
            ->withCount('users')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE WHEN slug = 'super-admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $permissions = Permission::query()->orderBy('slug')->get();

        return view('roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRole($request);
        abort_if($validated['slug'] === 'super-admin' && ! $request->user()->hasRole('super-admin'), 403);

        DB::transaction(function () use ($validated) {
            $role = Role::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'is_active' => (bool) $validated['is_active'],
            ]);

            $role->permissions()->sync($validated['permissions'] ?? []);
        });

        return redirect()->route('roles.index')->with('success', 'Role berhasil ditambahkan.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $this->validateRole($request, $role);
        $isSuperAdmin = $role->slug === 'super-admin';
        abort_if(
            ($isSuperAdmin || $validated['slug'] === 'super-admin') && ! $request->user()->hasRole('super-admin'),
            403,
        );

        DB::transaction(function () use ($role, $validated, $isSuperAdmin) {
            $role->update([
                'name' => $validated['name'],
                'slug' => $isSuperAdmin ? 'super-admin' : $validated['slug'],
                'description' => $validated['description'] ?? null,
                'is_active' => $isSuperAdmin ? true : (bool) $validated['is_active'],
            ]);

            $role->permissions()->sync($validated['permissions'] ?? []);
        });

        return redirect()->route('roles.index')->with('success', 'Role dan permission berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->slug === 'super-admin') {
            return back()->with('error', 'Role Super Admin tidak dapat dihapus.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Role masih digunakan oleh user dan tidak dapat dihapus.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus.');
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9]+(?:[.-][a-z0-9]+)*$/',
                Rule::unique('roles', 'slug')->ignore($role?->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'distinct', 'exists:permissions,id'],
        ], [
            'slug.regex' => 'Kode role hanya boleh berisi huruf kecil, angka, titik, dan tanda hubung.',
        ]);
    }
}
