<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MitraSBPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/mitra_sbp.csv');

        if (!File::exists($path)) {
            $this->command->error("CSV file not found: {$path}");
            return;
        }

        $rows = array_map('str_getcsv', file($path));

        // Ambil header & buang dari data
        $header = array_map('trim', $rows[0]);
        unset($rows[0]);

        foreach ($rows as $row) {
            if (count($row) < 4) {
                continue; // skip baris tidak valid
            }

            DB::table('mitra_sbp')->insert([
                'email_myads' => trim($row[0]),
                'area'        => trim($row[1]),
                'remark'      => trim($row[2]),
                'voucher_83'  => trim($row[3]),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
