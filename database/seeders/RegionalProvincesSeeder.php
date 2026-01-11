<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RegionalProvincesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            // SUMBAGSEL
            ['regional' => 'SUMBAGSEL', 'province' => 'Sumatera Selatan'],
            ['regional' => 'SUMBAGSEL', 'province' => 'Jambi'],
            ['regional' => 'SUMBAGSEL', 'province' => 'Bengkulu'],
            ['regional' => 'SUMBAGSEL', 'province' => 'Lampung'],
            ['regional' => 'SUMBAGSEL', 'province' => 'Bangka Belitung'],

            // SUMBAGTENG
            ['regional' => 'SUMBAGTENG', 'province' => 'Sumatera Barat'],
            ['regional' => 'SUMBAGTENG', 'province' => 'Riau'],
            ['regional' => 'SUMBAGTENG', 'province' => 'Kepulauan Riau'],

            // SUMBAGUT
            ['regional' => 'SUMBAGUT', 'province' => 'Sumatera Utara'],
            ['regional' => 'SUMBAGUT', 'province' => 'Nangroe Aceh'],

            // JABODETABEK
            ['regional' => 'JABODETABEK', 'province' => 'DKI Jakarta'],
            ['regional' => 'JABODETABEK', 'province' => 'Banten'],

            // JABAR
            ['regional' => 'JABAR', 'province' => 'Jawa Barat'],

            // JATENG DIY
            ['regional' => 'JATENG DIY', 'province' => 'Jawa Tengah'],
            ['regional' => 'JATENG DIY', 'province' => 'Yogyakarta'],

            // JATIM
            ['regional' => 'JATIM', 'province' => 'Jawa Timur'],

            // BALI NUSRA
            ['regional' => 'BALI NUSRA', 'province' => 'Bali'],
            ['regional' => 'BALI NUSRA', 'province' => 'NTB'],
            ['regional' => 'BALI NUSRA', 'province' => 'NTT'],

            // KALIMANTAN
            ['regional' => 'KALIMANTAN', 'province' => 'Kalimantan Tengah'],
            ['regional' => 'KALIMANTAN', 'province' => 'Kalimantan Barat'],
            ['regional' => 'KALIMANTAN', 'province' => 'Kalimantan Utara'],
            ['regional' => 'KALIMANTAN', 'province' => 'Kalimantan Timur'],
            ['regional' => 'KALIMANTAN', 'province' => 'Kalimantan Selatan'],

            // SULAWESI
            ['regional' => 'SULAWESI', 'province' => 'Sulawesi Utara'],
            ['regional' => 'SULAWESI', 'province' => 'Sulawesi Tengah'],
            ['regional' => 'SULAWESI', 'province' => 'Gorontalo'],
            ['regional' => 'SULAWESI', 'province' => 'Sulawesi Tenggara'],
            ['regional' => 'SULAWESI', 'province' => 'Sulawesi Selatan'],
            ['regional' => 'SULAWESI', 'province' => 'Maluku Utara'],

            // PAPUA MALUKU
            ['regional' => 'Papua Maluku', 'province' => 'Maluku'],
            ['regional' => 'Papua Maluku', 'province' => 'Papua Barat'],
            ['regional' => 'Papua Maluku', 'province' => 'Papua'],
        ];

        DB::table('regional_provinces')->insert(
            array_map(function ($item) use ($now) {
                return array_merge($item, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }, $data)
        );
    }
}
