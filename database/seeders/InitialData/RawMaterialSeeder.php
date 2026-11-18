<?php

namespace Database\Seeders\InitialData;

use App\Models\Barang;
use App\Models\Satuan;
use Database\Seeders\SatuanSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RawMaterialSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->call(SatuanSeeder::class);
            $units = Satuan::pluck('nama')->all();
            $materials = require __DIR__.'/raw_materials.php';

            foreach ($materials as [$name, $unit, $price]) {
                if (! in_array($unit, $units, true)) {
                    throw new RuntimeException("Satuan {$unit} untuk {$name} belum terdaftar.");
                }

                $item = Barang::firstOrCreate([
                    'nama_barang' => $name,
                    'kategori' => 'produksi',
                ], [
                    'sub_kategori' => 'bahan_baku',
                    'satuan' => $unit,
                    'harga' => $price,
                    'status' => 'aktif',
                ]);

                if ($item->sub_kategori !== 'bahan_baku' || $item->satuan !== $unit) {
                    throw new RuntimeException("Bahan {$name} sudah ada dengan sub kategori atau satuan berbeda.");
                }
            }
        });
    }
}
