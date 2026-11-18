<?php

namespace Database\Seeders\InitialData;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\Satuan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = require __DIR__.'/catalog.php';

        DB::transaction(function () use ($catalog) {
            $units = Satuan::konversiStok();
            $items = [];

            foreach ($catalog['barang'] as $name => [$category, $unit]) {
                if (($units[$unit]['dasar'] ?? null) !== $unit) {
                    throw new RuntimeException("Satuan dasar {$unit} untuk {$name} tidak sesuai dengan master satuan.");
                }

                $item = Barang::firstOrCreate(['nama_barang' => $name, 'kategori' => 'jadi'], [
                    'sub_kategori' => $category,
                    'satuan' => $unit,
                    'status' => 'aktif',
                ]);

                if ($item->sub_kategori !== $category || ($units[$item->satuan]['dasar'] ?? null) !== $unit) {
                    throw new RuntimeException("Barang {$name} sudah ada dengan sub kategori atau satuan dasar berbeda.");
                }

                $items[$name] = $item;
            }

            foreach ($catalog['sub_barang'] as $customer => $variants) {
                $customerId = Customer::where('name', $customer)->value('id');
                if ($customerId === null) {
                    throw new RuntimeException("Customer {$customer} tidak ditemukan.");
                }

                foreach ($variants as [$name, $label, $unit, $price]) {
                    $item = $items[$name];
                    $base = $units[$unit]['dasar'] ?? null;

                    if ($base === null || $base !== $units[$item->satuan]['dasar']) {
                        throw new RuntimeException("Satuan {$unit} untuk {$customer} / {$label} tidak sesuai dengan {$name}.");
                    }

                    $item->sub_barang()->firstOrCreate([
                        'customer_id' => $customerId,
                        'sub_nama' => $label,
                    ], [
                        'satuan' => $unit,
                        'satuan_terkecil' => $base,
                        'harga' => $price,
                        'status' => 'aktif',
                    ]);
                }
            }
        });
    }
}
