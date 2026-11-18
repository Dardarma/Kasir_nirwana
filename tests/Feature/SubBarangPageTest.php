<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class SubBarangPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_sub_barang_page_loads_with_no_products(): void
    {
        $this->actingAs($this->viewer())
            ->get(route('sub_barang.index'))
            ->assertOk();
    }

    public function test_sub_barang_page_loads_existing_product_and_its_master_item(): void
    {
        // Insert through the query builder so the Barang model autoloads during the request.
        $barangId = DB::table('barang')->insertGetId([
            'kategori' => 'jadi',
            'sub_kategori' => 'barang_jadi',
            'satuan' => 'gram',
            'nama_barang' => 'Nugget Master',
        ]);

        $customer = Customer::create(['name' => 'Customer Uji', 'status' => 'aktif']);
        DB::table('sub_barang')->insert([
            'barang_id' => $barangId,
            'customer_id' => $customer->id,
            'sub_nama' => 'Nugget Kemasan',
            'satuan' => 'gram',
            'satuan_terkecil' => 'gram',
            'harga' => 5000,
        ]);

        $this->actingAs($this->viewer())
            ->get(route('sub_barang.index'))
            ->assertOk()
            ->assertSee('Nugget Kemasan')
            ->assertSee('Nugget Master');
    }

    private function viewer(): User
    {
        $role = Role::create(['name' => 'Lihat Sub Barang', 'slug' => 'lihat-sub-barang', 'is_active' => true]);
        $permission = Permission::create(['name' => 'Lihat Sub Barang', 'slug' => 'sub-barang.view']);
        $role->permissions()->attach($permission);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
