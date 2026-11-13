<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect(config('access.permissions'))
            ->map(fn (array $permission, string $slug) => Permission::updateOrCreate(
                ['slug' => $slug],
                $permission,
            ));

        $role = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Memiliki seluruh akses aplikasi.',
                'is_active' => true,
            ],
        );

        $role->permissions()->syncWithoutDetaching($permissions->pluck('id'));

        $password = config('auth.initial_admin.password');

        if (blank($password)) {
            $this->command?->warn('Admin awal belum dibuat. Isi INITIAL_ADMIN_PASSWORD pada .env lalu jalankan seeder kembali.');

            return;
        }

        $user = User::firstOrCreate(
            ['username' => config('auth.initial_admin.username')],
            [
                'name' => config('auth.initial_admin.name'),
                'password' => $password,
                'status' => 'aktif',
            ],
        );

        $user->roles()->syncWithoutDetaching([$role->id]);
    }
}
