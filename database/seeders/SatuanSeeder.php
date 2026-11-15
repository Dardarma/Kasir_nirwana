<?php

namespace Database\Seeders;

use App\Models\Satuan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatuanSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $gram = Satuan::firstOrCreate(['nama' => 'gram'], ['is_satuan_terkecil' => true]);

            foreach (['kg' => 1000, 'pcs(500g)' => 500, 'pcs(250g)' => 250] as $nama => $faktor) {
                $unit = Satuan::firstOrCreate(['nama' => $nama], ['parent_id' => $gram->id, 'faktor' => $faktor]);
                if ($unit->parent_id === null && $unit->faktor === null && ! $unit->is_satuan_terkecil) {
                    $unit->update(['parent_id' => $gram->id, 'faktor' => $faktor]);
                }
            }

            $ml = Satuan::firstOrCreate(['nama' => 'ml'], ['is_satuan_terkecil' => true]);
            Satuan::firstOrCreate(['nama' => 'liter'], ['parent_id' => $ml->id, 'faktor' => 1000]);

            foreach (['hari', 'minggu', 'bulan', 'borongan'] as $nama) {
                Satuan::firstOrCreate(['nama' => $nama]);
            }
        });
    }
}
