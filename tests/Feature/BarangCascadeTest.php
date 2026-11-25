<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\sub_barang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangCascadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivation_cascades_only_to_related_sub_items_and_does_not_reactivate_them(): void
    {
        $customer = Customer::create(['name' => 'Umum', 'status' => 'aktif']);
        $first = Barang::create(['nama_barang' => 'A', 'kategori' => 'jadi', 'sub_kategori' => 'barang_jadi', 'satuan' => 'gram', 'status' => 'aktif']);
        $other = Barang::create(['nama_barang' => 'B', 'kategori' => 'jadi', 'sub_kategori' => 'barang_jadi', 'satuan' => 'gram', 'status' => 'aktif']);

        foreach ([$first, $first, $other] as $index => $barang) {
            sub_barang::create([
                'barang_id' => $barang->id, 'customer_id' => $customer->id,
                'sub_nama' => 'Sub '.$index, 'satuan' => 'gram', 'satuan_terkecil' => 'gram',
                'harga' => 1000, 'status' => 'aktif',
            ]);
        }

        $first->update(['nama_barang' => 'A Baru']);
        $this->assertSame(2, $first->sub_barang()->where('status', 'aktif')->count());

        $first->status = 'nonaktif';
        $first->save();
        $this->assertSame(2, $first->sub_barang()->where('status', 'nonaktif')->count());
        $this->assertSame(1, $other->sub_barang()->where('status', 'aktif')->count());
        $this->assertDatabaseCount('sub_barang', 3);

        $first->update(['status' => 'aktif']);
        $this->assertSame(2, $first->sub_barang()->where('status', 'nonaktif')->count());
    }
}
