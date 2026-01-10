<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\LeadsMaster;
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

            // -----------------------------
            // Normalize & fallback values
            // -----------------------------
            $normalizedPhone = $this->normalizePhone($phone);
            if ($normalizedPhone === '' || $normalizedPhone === null) {
                $normalizedPhone = '-';
            }

            if (empty($email)) {
                continue;
            }

            $userId   = $this->getUserIdByName($handledBy);
            $sourceId = $this->getSourceIdByName($source);

            // -----------------------------
            // INSERT (NO SKIP)
            // -----------------------------
            LeadsMaster::create([
                'user_id'      => $userId,
                'source_id'    => $sourceId,
                'sector_id'    => 1,
                'regional'     => '-',
                'kode_voucher' => $kodeVoucher ?: '-',
                'company_name' => $company ?: '-',
                'mobile_phone' => $normalizedPhone,
                'email'        => $email,
                'status'       => str_contains($cek ?? '', 'OK') ? 1 : 0,
                'nama'         => $nama ?: '-',
                'data_type'    => 'Eksisting Akun',
                'remarks'      => $remarks ?: '-',
            ]);
        }

        fclose($handle);
    }

    // ----------------------------------
    // USER (always returns valid ID)
    // ----------------------------------
    private function getUserIdByName(?string $name): int
    {
        if (empty($name)) {
            return 1; // default user
        }

        $name = trim($name);

        if (isset($this->userCache[$name])) {
            return $this->userCache[$name];
        }

        $user = User::firstOrCreate(
            ['name' => $name],
            [
                'email'    => strtolower(str_replace(' ', '.', $name)) . '@gmail.com',
                'password' => Hash::make('123456'),
                'role'     => 'cvsr',
            ]
        );

        return $this->userCache[$name] = $user->id;
    }

    // ----------------------------------
    // SOURCE (always returns valid ID)
    // ----------------------------------
    private function getSourceIdByName(?string $name): int
    {
        if (empty($name)) {
            return 1; // Data Sendiri
        }

        $name = trim($name);

        if (isset($this->sourceCache[$name])) {
            return $this->sourceCache[$name];
        }

        $source = DB::table('leads_source')
            ->where('name', $name)
            ->first();

        return $this->sourceCache[$name] = $source?->id ?? 1;
    }

    // ----------------------------------
    // PHONE NORMALIZER
    // ----------------------------------
    private function normalizePhone($phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', $phone);
    }
}
