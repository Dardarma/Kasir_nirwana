<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $users = User::query()
            ->with('roles')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $canManageRoles = $request->user()->hasPermission('roles.manage');
        $roles = $canManageRoles
            ? Role::query()->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('users.index', compact('users', 'roles', 'canManageRoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['aktif', 'non_aktif'])],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        abort_if(! $request->user()->hasPermission('roles.manage') && ! empty($validated['roles']), 403);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => $validated['password'],
            'status' => $validated['status'],
        ]);

        if ($request->user()->hasPermission('roles.manage')) {
            $user->roles()->sync($validated['roles'] ?? []);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->username === null, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['aktif', 'non_aktif'])],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        abort_if(! $request->user()->hasPermission('roles.manage') && ! empty($validated['roles']), 403);

        if ($request->user()->is($user) && $validated['status'] !== 'aktif') {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        }

        $attributes = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $attributes['password'] = $validated['password'];
        }

        $user->update($attributes);
        if ($request->user()->hasPermission('roles.manage')) {
            $user->roles()->sync($validated['roles'] ?? []);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->username === null, 404);

        if ($request->user()->is($user)) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        }

        $user->update(['status' => 'non_aktif']);

        return redirect()->route('users.index')->with('success', 'User berhasil dinonaktifkan.');
    }
}
