<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\LeadsMaster;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LeadsSeeder extends Seeder
{
    protected array $userCache = [];
    protected array $sourceCache = [];

    public function run()
    {
        $handle = fopen(storage_path('app/leads.csv'), 'r');

        // Skip header
        fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {

            [
                $handledBy,
                $kodeVoucher,
                $company,
                $phone,
                $email,
                $countEmail,
                $cek,
                $source,
                $nama,
                $sector,
                $remarks
            ] = array_pad($row, 11, null);

            if (!$company || !$phone) {
                continue;
            }

            // Duplicate leads
            if (
                LeadsMaster::where('mobile_phone', $this->normalizePhone($phone))
                    ->orWhere('email', $email)
                    ->exists()
            ) {
                continue;
            }

            $userId   = $this->getUserIdByName($handledBy);
            $sourceId = $this->getSourceIdByName($source);

            LeadsMaster::create([
                'user_id'      => $userId,
                'source_id'    => $sourceId,
                'sector_id'    => 1,
                'kode_voucher' => $kodeVoucher,
                'company_name' => $company,
                'mobile_phone' => $this->normalizePhone($phone),
                'email'        => $email,
                'status'       => str_contains($cek, 'OK') ? 1 : 0,
                'nama'         => $nama,
                'remarks'      => $remarks,
            ]);
        }

        fclose($handle);
    }

    // 🔑 USER
    private function getUserIdByName(?string $name): int
    {
        if (!$name) {
            return 1;
        }

        $name = trim($name);

        if (isset($this->userCache[$name])) {
            return $this->userCache[$name];
        }

        $user = User::firstOrCreate(
            ['name' => $name],
            [
                'email' => strtolower(str_replace(' ', '.', $name)) . '@auto.local',
                'password' => Hash::make('123456'),
            ]
        );

        return $this->userCache[$name] = $user->id;
    }

    // 🔑 LEADS SOURCE (MATCHES LeadSourceSeeder)
    private function getSourceIdByName(?string $name): int
    {
        if (!$name) {
            return 1; // default: Data Sendiri
        }

        $name = trim($name);

        if (isset($this->sourceCache[$name])) {
            return $this->sourceCache[$name];
        }

        $source = DB::table('leads_source')
            ->where('name', $name)
            ->first();

        // fallback if source not found
        return $this->sourceCache[$name] = $source?->id ?? 1;
    }

    private function normalizePhone($phone)
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}

