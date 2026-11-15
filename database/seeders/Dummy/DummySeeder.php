<?php

namespace Database\Seeders\Dummy;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Satuan;
use App\Models\User;
use App\Models\sub_barang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DummySeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('DummySeeder hanya untuk lingkungan local atau testing.');
        }

        DB::transaction(function () {
            $permissions = Permission::whereIn('slug', array_keys(config('access.permissions')))->pluck('id', 'slug');
            $superAdmin = Role::where('slug', 'super-admin')->first();
            if (! $superAdmin || $permissions->count() !== count(config('access.permissions'))) {
                throw new RuntimeException('Jalankan php artisan db:seed sebelum DummySeeder.');
            }

            $kasir = Role::firstOrCreate(['slug' => 'kasir'], ['name' => 'Kasir']);
            $kasir->permissions()->sync($permissions->reject(
                fn ($id, $slug) => str_starts_with($slug, 'users.') || $slug === 'roles.manage'
            )->values());

            $adminUser = Role::firstOrCreate(['slug' => 'admin-user'], ['name' => 'Admin User']);
            $adminUser->permissions()->sync($permissions->filter(
                fn ($id, $slug) => str_starts_with($slug, 'users.')
            )->values());

            foreach (['dummy-superadmin' => $superAdmin, 'dummy-kasir' => $kasir, 'dummy-admin-user' => $adminUser] as $username => $role) {
                $user = User::firstOrCreate(['username' => $username], [
                    'name' => 'Dummy ' . $role->name,
                    'password' => 'KasirDummy123!',
                    'status' => 'aktif',
                ]);
                $user->roles()->sync([$role->id]);
            }

            $barang = Barang::firstOrCreate(['nama_barang' => 'Nugget', 'kategori' => 'jadi'], [
                'sub_kategori' => 'barang_jadi',
                'satuan' => 'gram',
            ]);

            foreach (['Umum' => ['pcs(500g)', 17000], 'RS' => ['kg', 70000]] as $nama => [$satuan, $harga]) {
                $customer = Customer::firstOrCreate(['name' => $nama]);
                sub_barang::firstOrCreate([
                    'barang_id' => $barang->id,
                    'customer_id' => $customer->id,
                    'sub_nama' => 'Nugget ' . $nama,
                ], [
                    'satuan' => $satuan,
                    'satuan_terkecil' => Satuan::dasarStok($satuan)[0],
                    'harga' => $harga,
                ]);
            }
        });
    }
}
