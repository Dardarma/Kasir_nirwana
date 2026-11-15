<?php

namespace Tests\Feature;

use App\Http\Requests\ConfirmTransactionDeletion;
use App\Models\Barang;
use App\Models\Customer;
use App\Models\sub_barang;
use App\Models\Role;
use App\Models\Transaksi;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Barang $barang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->barang = Barang::create([
            'nama_barang' => 'Tepung',
            'satuan' => 'kg',
            'kategori' => 'produksi',
            'sub_kategori' => 'bahan_baku',
            'harga' => 1000,
        ]);
    }

    public function test_transaction_creation_requires_login_and_permission(): void
    {
        $payload = $this->payload([$this->item('1', 'kg')]);

        $this->postJson(route('pemakaian.store'), $payload)->assertUnauthorized();
        $this->actingAs(User::factory()->create())
            ->postJson(route('pemakaian.store'), $payload)->assertForbidden();

        $this->assertDatabaseCount('transaksi', 0);
    }

    public function test_incompatible_units_and_excess_precision_are_rejected(): void
    {
        $this->signInAsSuperAdmin();

        $this->postJson(route('pembelian.store'), $this->payload([$this->item('1', 'liter')]))
            ->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->postJson(route('pemakaian.store'), $this->payload([$this->item('1.001', 'kg')]))
            ->assertUnprocessable()->assertJsonValidationErrors('items.0.jumlah');

        $this->assertDatabaseCount('transaksi', 0);
        $this->assertDatabaseCount('stok_masuk', 0);
        $this->assertDatabaseCount('stok_keluar', 0);
    }

    public function test_pemakaian_rejects_combined_quantity_above_available_stock(): void
    {
        $this->signInAsSuperAdmin();
        $this->purchaseStock();

        $this->postJson(route('pemakaian.store'), $this->payload([
            $this->item('0.70', 'kg'),
            $this->item('600', 'gram'),
        ]))->assertUnprocessable()->assertJsonValidationErrors('items');

        $this->assertDatabaseCount('transaksi', 1);
        $this->assertDatabaseCount('stok_keluar', 0);
        $this->assertEquals(1250, get_stok_by_id($this->barang->id));
    }

    public function test_pemakaian_records_conversion_and_cancellation_restores_stock_once(): void
    {
        $this->signInAsSuperAdmin();
        $this->purchaseStock();

        $this->postJson(route('pemakaian.store'), $this->payload([$this->item('0.15', 'kg')]))
            ->assertOk();
        $transaksi = Transaksi::where('jenis_transaksi', 'pemakaian')->sole();

        $this->assertDatabaseHas('transaksi_detail', [
            'transaksi_id' => $transaksi->id,
            'jumlah_gram' => 150,
            'satuan_stok' => 'gram',
            'faktor_satuan' => 1000,
        ]);
        $this->assertEquals(1100, get_stok_by_id($this->barang->id));

        $url = route('pemakaian.destroy', $transaksi->id);
        $this->putJson($url)->assertUnprocessable()->assertJsonValidationErrors('konfirmasi_hapus');
        $this->assertEquals(1100, get_stok_by_id($this->barang->id));

        $confirmation = ['konfirmasi_hapus' => ConfirmTransactionDeletion::PHRASE];
        $this->put($url, $confirmation)->assertSessionHas('success');
        $this->put($url, $confirmation)->assertSessionHas('success');

        $this->assertDatabaseHas('transaksi', ['id' => $transaksi->id, 'status' => 'dibatalkan']);
        $this->assertDatabaseCount('stok_keluar', 0);
        $this->assertEquals(1250, get_stok_by_id($this->barang->id));
    }

    public function test_purchase_detail_is_not_available_through_cashier_route(): void
    {
        $this->signInAsSuperAdmin();
        $this->purchaseStock();
        $id = Transaksi::where('jenis_transaksi', 'pembelian')->sole()->id;

        $this->getJson("/kasir/{$id}/detail")->assertNotFound();
    }

    public function test_purchase_cancellation_rejects_insufficient_stock_and_can_be_retried(): void
    {
        $this->signInAsSuperAdmin();
        $this->purchaseStock();
        $purchase = Transaksi::where('jenis_transaksi', 'pembelian')->sole();
        $this->postJson(route('pemakaian.store'), $this->payload([$this->item('0.25', 'kg')]))
            ->assertOk();
        $usage = Transaksi::where('jenis_transaksi', 'pemakaian')->sole();

        $confirmation = ['konfirmasi_hapus' => ConfirmTransactionDeletion::PHRASE];
        $url = route('pembelian.destroy', $purchase->id);
        $this->putJson($url, $confirmation)->assertUnprocessable()
            ->assertJsonValidationErrors('konfirmasi_hapus');
        $this->assertSame('aktif', $purchase->fresh()->status);
        $this->assertEquals(1000, get_stok_by_id($this->barang->id));

        $this->put(route('pemakaian.destroy', $usage->id), $confirmation)->assertSessionHas('success');
        $this->put($url, $confirmation)->assertSessionHas('success');
        $this->put($url, $confirmation)->assertSessionHas('success');

        $this->assertSame('dibatalkan', $purchase->fresh()->status);
        $this->assertDatabaseCount('stok_masuk', 0);
        $this->assertEquals(0, get_stok_by_id($this->barang->id));
        $this->putJson(route('pembelian.update', $purchase->id), ['customer_bayar' => 1250])
            ->assertUnprocessable();
        $this->assertSame('belum_lunas', $purchase->fresh()->status_pembayaran);
    }

    public function test_sale_cancellation_restores_stock_and_blocks_payment_updates(): void
    {
        $this->signInAsSuperAdmin();
        $this->purchaseStock();
        $customer = Customer::create(['name' => 'Umum']);
        $subBarang = sub_barang::create([
            'barang_id' => $this->barang->id,
            'customer_id' => $customer->id,
            'sub_nama' => 'Tepung 250g',
            'satuan' => 'pcs(250g)',
            'satuan_terkecil' => 'gram',
            'harga' => 1000,
            'status' => 'aktif',
        ]);

        $item = $this->item('1', 'pcs(250g)') + [
            'sub_barang_id' => $subBarang->id,
            'jumlah_gram' => 250,
        ];
        $this->postJson(route('kasir.store'), $this->payload([$item]) + ['customer_id' => $customer->id])
            ->assertOk();
        $sale = Transaksi::where('jenis_transaksi', 'penjualan')->sole();
        $this->assertEquals(1000, get_stok_by_id($this->barang->id));

        $this->put(route('kasir.destroy', $sale->id), [
            'konfirmasi_hapus' => ConfirmTransactionDeletion::PHRASE,
        ])->assertSessionHas('success');

        $this->assertSame('dibatalkan', $sale->fresh()->status);
        $this->assertDatabaseCount('stok_keluar', 0);
        $this->assertEquals(1250, get_stok_by_id($this->barang->id));
        $this->putJson(route('kasir.update', $sale->id), ['customer_bayar' => 1000])
            ->assertUnprocessable();
        $this->assertSame('belum_lunas', $sale->fresh()->status_pembayaran);
    }

    public function test_barang_can_be_deactivated_using_the_schema_status(): void
    {
        $this->signInAsSuperAdmin();

        $this->put(route('Barang_destroy', $this->barang->id))->assertSessionHas('success');
        $this->assertSame('nonaktif', $this->barang->fresh()->status);
    }

    private function signInAsSuperAdmin(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'super-admin')->sole());
        $this->actingAs($user);
    }

    private function purchaseStock(): void
    {
        $this->postJson(route('pembelian.store'), $this->payload([$this->item('1.25', 'kg')]))
            ->assertOk();
        $this->assertEquals(1250, get_stok_by_id($this->barang->id));
    }

    private function item(string $jumlah, string $satuan): array
    {
        return [
            'barang_id' => $this->barang->id,
            'nama_barang' => $this->barang->nama_barang,
            'jumlah' => $jumlah,
            'satuan' => $satuan,
            'harga' => 1000,
        ];
    }

    private function payload(array $items): array
    {
        return [
            'tanggal' => '2026-11-15',
            'bayar' => 0,
            'customer_bayar' => 0,
            'metode_pembayaran' => 'tunai',
            'items' => $items,
        ];
    }
}
