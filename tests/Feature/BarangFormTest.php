<?php

namespace Tests\Feature;

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

    public function test_finished_item_saves_without_price_or_sub_item(): void
    {
        $this->actingAs($this->creator())->post(route('Barang_store'), [
            'nama_barang' => 'Nugget',
            'kategori' => 'jadi',
            'satuan' => 'gram',
            'sub_kategori' => 'barang_jadi',
        ])->assertRedirect('/barang')->assertSessionHasNoErrors();

        $this->assertDatabaseHas('barang', ['nama_barang' => 'Nugget', 'harga' => null]);
        $this->assertDatabaseCount('sub_barang', 0);
    }

    public function test_edit_ignores_price_and_sub_item_fields(): void
    {
        $user = $this->creator();
        $barang = \App\Models\Barang::create([
            'nama_barang' => 'Nugget', 'kategori' => 'jadi',
            'sub_kategori' => 'barang_jadi', 'satuan' => 'gram',
            'harga' => 1000, 'status' => 'aktif',
        ]);
        $this->actingAs($user)->put(route('Barang_update', $barang->id), [
            'nama_barang' => 'Nugget Baru', 'kategori' => 'jadi',
            'sub_kategori' => 'barang_jadi', 'satuan' => 'gram',
            'harga' => 5000, 'sub_nama' => 'Tidak dibuat',
        ])->assertRedirect('/barang')->assertSessionHasNoErrors();

        $this->assertDatabaseHas('barang', ['id' => $barang->id, 'nama_barang' => 'Nugget Baru', 'harga' => 1000]);
        $this->assertDatabaseCount('sub_barang', 0);
    }

    private function creator(): User
    {
        Satuan::create(['nama' => 'gram', 'is_satuan_terkecil' => true]);
        $role = Role::create(['name' => 'Buat Barang', 'slug' => 'buat-barang', 'is_active' => true]);
        foreach (['barang.create', 'barang.update'] as $slug) {
            $role->permissions()->attach(Permission::create(['name' => $slug, 'slug' => $slug]));
        }

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
