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

            $hari = Satuan::firstOrCreate(['nama' => 'hari']);
            foreach (['minggu' => 7, 'bulan' => 30] as $nama => $faktor) {
                Satuan::firstOrCreate(['nama' => $nama], ['parent_id' => $hari->id, 'faktor' => $faktor]);
            }

            $biji = Satuan::firstOrCreate(['nama' => 'biji'], ['is_satuan_terkecil' => true]);
            foreach (['pcs (ebi furay)' => 10, 'pcs (pempek)' => 5, 'pcs(dimsum)' => 9, 'pcs (tahu)' => 8] as $nama => $faktor) {
                Satuan::firstOrCreate(['nama' => $nama], ['parent_id' => $biji->id, 'faktor' => $faktor]);
            }

            foreach (['borongan', 'botol', 'pcs', 'ekor', 'ekor(250g)', 'ekor(200g)', 'ekor(175g)', 'ekor(150g)', 'rp', 'papan'] as $nama) {
                Satuan::firstOrCreate(['nama' => $nama], ['is_satuan_terkecil' => true]);
            }
        });
    }
}
