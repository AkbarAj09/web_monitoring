<?php

namespace App\Http\Controllers;

use App\Models\RahasiaBisnis;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Carbon\Carbon;
use File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use ZipArchive;

class BackController extends Controller
{
    public function registerStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'nope' => 'required',
            'email' => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@telkomsel\.co\.id$/'],
            'role' => 'required|in:Admin,Tsel',
        ]);
        $data = [
            'name' => $request->name,
            'nohp' => $request->nope,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make('123456'),
            'status' => 'Aktif'
        ];
        $nope = $request->nope;
        if (Str::startsWith($nope, '08')) {
            $nope = '62' . substr($nope, 1);
        }
        $data['nohp'] = $nope;
        User::create($data);
        return redirect()->back()->with('success', 'User berhasil didaftarkan!');
    }
    public function login(Request $request)
    {
        // 1. Validasi form
        $request->validate([
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@telkomsel\.co\.id$/', // hanya email @telkomsel.co.id
            ],
            'password' => 'required',
        ], [
            'email.regex' => 'Email harus menggunakan domain @telkomsel.co.id',
        ]);

        // 2. Coba login
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // 3. Cek status
            if ($user->status !== 'Aktif') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda belum aktif.',
                ])->withInput();
            }

            // 4. Arahkan sesuai role
            switch ($user->role) {
                case 'Admin':
                    return redirect()->route('admin.home'); // ganti dengan route admin
                case 'Tsel':
                    return redirect()->route('tsel.home'); // ganti dengan route tsel
                default:
                    return redirect()->route('home'); // fallback
            }
        }

        // 5. Kalau gagal login
        return back()->withErrors([
            'email' => 'Email atau Password Anda salah.',
        ])->withInput();
    }
    public function getDataRahasiaBisnis()
    {
        $url = "https://docs.google.com/spreadsheets/d/189GWwmBkByfgraTxSDjKbcmH9zjE8a83aIKtCYnpyq8/export?format=csv&gid=1498573724";

        try {
            $csv = @array_map('str_getcsv', file($url));
        } catch (\Exception $e) {
            Log::channel('rahasiaBisnis')->error("Gagal mengambil file CSV: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal ambil CSV'], 500);
        }

        if (empty($csv)) {
            Log::channel('rahasiaBisnis')->warning("CSV kosong dari URL: $url");
            return response()->json(['status' => 'error', 'message' => 'CSV kosong'], 400);
        }

        $header = array_map(fn($h) => Str::slug($h, '_'), $csv[0]);
        unset($csv[0]);

        $inserted = 0;

        foreach ($csv as $row) {
            if (count($row) < count($header)) continue;

            $rowAssoc = array_combine($header, $row);

            // ambil timestamp
            if (!empty($rowAssoc['timestamp'])) {
                try {
                    $created_at = Carbon::createFromFormat('d/m/Y H:i:s', $rowAssoc['timestamp'])
                        ->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $created_at = now();
                    Log::channel('rahasiaBisnis')->warning("Gagal parsing timestamp: {$rowAssoc['timestamp']}");
                }
            } else {
                $created_at = now();
            }

            // cek hanya di kolom kabupaten yang valid
            $kabupaten_kota = null;
            foreach ($rowAssoc as $colName => $value) {
                $colNameLower = strtolower($colName);
                if (Str::startsWith($colNameLower, 'kabupaten') || Str::startsWith($colNameLower, 'kabupaten_kota')) {
                    if (!empty($value)) {
                        $kabupaten_kota = $value;
                        break;
                    }
                }
            }

            RahasiaBisnis::create([
                'nama'          => $rowAssoc['nama'] ?? null,
                'email'         => $rowAssoc['email'] ?? null,
                'nomor_hp'      => $rowAssoc['nomor_hp'] ?? null,
                'jenis_usaha'   => $rowAssoc['jenis_usaha'] ?? null,
                'alamat_usaha'  => $rowAssoc['alamat_usaha'] ?? null,
                'provinsi'      => $rowAssoc['provinsi'] ?? null,
                'kabupaten_kota' => $kabupaten_kota,
                'kecamatan'     => $rowAssoc['silakan_isi_kecamatan'] ?? null,
                'created_at'    => $created_at,
                'updated_at'    => now(),
            ]);

            $inserted++;
        }

        Log::channel('rahasiaBisnis')->info("Import selesai, berhasil insert {$inserted} data dari CSV.");

        return response()->json([
            'status' => 'success',
            'message' => "Import selesai! Total $inserted data berhasil dimasukkan"
        ], 201);
    }
}
