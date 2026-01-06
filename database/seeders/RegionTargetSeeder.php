<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RegionTargetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('region_target')->insert([
            'region_name'   => 'Sumbagsel',
            'pic'           => 'Angga Satria Gusti',
            'date'          => Carbon::create(2026, 1, 1)->format('Y-m-d'),
            'target_amount' => 278850000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('region_target')->insert([
            'region_name'   => 'Sumbagteng',
            'pic'           => '-',
            'date'          => Carbon::create(2026, 1, 1)->format('Y-m-d'),
            'target_amount' => 198000000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('region_target')->insert([
            'region_name'   => 'Sumbagut',
            'pic'           => 'Abdul Halim',
            'date'          => Carbon::create(2026, 1, 1)->format('Y-m-d'),
            'target_amount' => 282150000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('region_target')->insert([
            'region_name'   => 'Jabar',
            'pic'           => 'Raden Agie Satria Akbar',
            'date'          => Carbon::create(2026, 1, 1)->format('Y-m-d'),
            'target_amount' => 297000000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('region_target')->insert([
            'region_name'   => 'Jabodetabek',
            'pic'           => 'Sony Widjaya',
            'date'          => Carbon::create(2026, 1, 1)->format('Y-m-d'),
            'target_amount' => 1353000000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('region_target')->insert([
            'region_name'   => 'Jateng DIY',
            'pic'           => 'Deni Setiawan',
            'date'          => Carbon::create(2026, 1, 1)->format('Y-m-d'),
            'target_amount' => 140250000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('region_target')->insert([
            'region_name'   => 'Jatim',
            'pic'           => 'Muhammad Arief Syahbana',
            'date'          => Carbon::create(2026, 1, 1)->format('Y-m-d'),
            'target_amount' => 214500000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('region_target')->insert([
            'region_name'   => 'Bali Nusra',
            'pic'           => '-',
            'date'          => Carbon::create(2026, 1, 1)->format('Y-m-d'),
            'target_amount' => 66000000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('region_target')->insert([
            'region_name'   => 'Kalimantan',
            'pic'           => 'Naqsyabandi',
            'date'          => Carbon::create(2026, 1, 1)->format('Y-m-d'),
            'target_amount' => 198000000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('region_target')->insert([
            'region_name'   => 'Papua Maluku',
            'pic'           => '-',
            'date'          => Carbon::create(2026, 1, 1)->format('Y-m-d'),
            'target_amount' => 41250000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('region_target')->insert([
            'region_name'   => 'Sulawesi',
            'pic'           => 'Ikrar Dharmawan',
            'date'          => Carbon::create(2026, 1, 1)->format('Y-m-d'),
            'target_amount' => 231000000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
