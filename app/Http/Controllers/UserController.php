<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $this->protectSuperAdminRole($request, $validated['roles'] ?? []);

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
        $this->authorizeAccountChange($request, $user);

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
        $this->protectSuperAdminRole($request, $validated['roles'] ?? []);

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

        DB::transaction(function () use ($user, $attributes, $request, $validated) {
            $user->update($attributes);
            if ($request->user()->hasPermission('roles.manage')) {
                $user->roles()->sync($validated['roles'] ?? []);
            }
            if ($attributes['status'] === 'non_aktif') {
                $this->revokeSessions($user);
            }
        });

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAccountChange($request, $user);

        if ($request->user()->is($user)) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        }

        DB::transaction(function () use ($user) {
            $user->update(['status' => 'non_aktif']);
            $this->revokeSessions($user);
        });

        return redirect()->route('users.index')->with('success', 'User berhasil dinonaktifkan.');
    }

    private function authorizeAccountChange(Request $request, User $target): void
    {
        abort_if(
            ! $request->user()->hasRole('super-admin')
                && $target->roles()->where('roles.slug', 'super-admin')->exists(),
            403,
        );
    }

    private function protectSuperAdminRole(Request $request, array $roleIds): void
    {
        abort_if(
            ! $request->user()->hasRole('super-admin')
                && Role::where('slug', 'super-admin')->whereIn('id', $roleIds)->exists(),
            403,
        );
    }

    private function revokeSessions(User $user): void
    {
        $user->setRememberToken(null);
        $user->save();

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table'))
                ->where('user_id', $user->getKey())
                ->delete();
        }
    }
}
