<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MitraSBPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $filePath = storage_path('app/mitra_sbp.csv');

        if (!file_exists($filePath)) {
            $this->command->error("CSV file not found at {$filePath}");
            return;
        }

        $handle = fopen($filePath, 'r');

        // skip header
        fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {

            // pastikan jumlah kolom aman
            [$email_myads, $area, $remark, $voucher] = array_pad($row, 4, null);

            if (!$email_myads) {
                continue;
            }

            // optional: skip duplicate
            if (DB::table('mitra_sbp')->where('email_myads', $email_myads)->exists()) {
                continue;
            }

            DB::table('mitra_sbp')->insert([
                'email_myads' => trim($email_myads),
                'area'        => trim($area),
                'remark'      => trim($remark),
                'voucher'     => trim($voucher),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        fclose($handle);

        $this->command->info('CSV import mitra_sbp complete!');
    }
}
