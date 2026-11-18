<?php

namespace Database\Seeders\InitialData;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Yaumi',
            'SDIT Abu Bakar',
            'Suwondo',
            'RSUD Kayen(mamik)',
            'RSUD Amabarawa',
            'RSI Sultan Agung',
            'RSI Pati',
            'RSI NU Demak',
            'RSI Jepara',
            'RS TIdar Magelang',
            'RS Tentara',
            'RS Sarkies Kudus',
            'RS Rumani',
            'RS Permata Blora',
            'RS Nurusyifa',
            'RS Kumalasiwi',
            'RS Fastabiq',
            'RS Budi Agung Juwana',
            'RS As-suyutiyah',
            'RS AISYIYAH',
            'Reseller - Yuyun PAti',
            'Reseller - Yummy Annisa Food',
            'Reseller - Wiwin Pondok Cabe',
            'Reseller - Watik Kendal',
            'Reseller - Sulis Lubang Buaya',
            'Reseller - Sri Jaya',
            'Reseller - Retno Jogja',
            'Reseller - Puji Lestari',
            'Reseller - Nandika Jogja',
            'Reseller - Komala Cilenggang',
            'Reseller - Fida Semarang',
            'Reseller - Endah Jepara',
            'Reseller - Dedy Purwokerto',
            'Reseller - Arlin Blora',
            'Reseller - Anis Klaten',
            'Reseller - Ahyati Magelang',
            'Ponpes Insan Mulia',
            'PKU Mayong',
            'PKU Gubug',
            'PKU Blora',
            'Umum',
            'Masjid Ji',
        ];

        foreach ($names as $name) {
            Customer::firstOrCreate(['name' => $name], ['status' => 'aktif']);
        }
    }
}
