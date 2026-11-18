<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Satuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_item_saves_without_price(): void
    {
        $this->actingAs($this->creator())->post(route('Barang_store'), [
            'nama_barang' => 'Tepung',
            'kategori' => 'produksi',
            'satuan' => 'gram',
            'sub_kategori' => 'bahan_baku',
        ])->assertRedirect('/barang')->assertSessionHasNoErrors();

        $this->assertDatabaseHas('barang', ['nama_barang' => 'Tepung', 'harga' => null]);
    }

    public function test_finished_item_saves_with_its_sub_item(): void
    {
        $customer = Customer::create(['name' => 'Pelanggan Uji', 'status' => 'aktif']);
        $this->actingAs($this->creator())->post(route('Barang_store'), [
            'nama_barang' => 'Nugget',
            'kategori' => 'jadi',
            'satuan' => 'gram',
            'sub_nama' => 'Nugget Kemasan',
            'customer_id' => $customer->id,
            'harga' => 5000,
        ])->assertRedirect('/barang')->assertSessionHasNoErrors();

        $barang = \App\Models\Barang::where('nama_barang', 'Nugget')->firstOrFail();
        $this->assertDatabaseHas('sub_barang', [
            'barang_id' => $barang->id,
            'customer_id' => $customer->id,
            'sub_nama' => 'Nugget Kemasan',
            'satuan_terkecil' => 'gram',
            'harga' => 5000,
        ]);
    }

    private function creator(): User
    {
        Satuan::create(['nama' => 'gram', 'is_satuan_terkecil' => true]);
        $role = Role::create(['name' => 'Buat Barang', 'slug' => 'buat-barang', 'is_active' => true]);
        foreach (['barang.create', 'sub-barang.create'] as $slug) {
            $role->permissions()->attach(Permission::create(['name' => $slug, 'slug' => $slug]));
        }

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
