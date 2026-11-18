<?php

namespace Database\Seeders\InitialData;

use Database\Seeders\SatuanSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->call([
                SatuanSeeder::class,
                CustomerSeeder::class,
                CatalogSeeder::class,
                RawMaterialSeeder::class,
            ]);
        });
    }
}
