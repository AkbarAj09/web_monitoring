<?php

namespace App\Http\Controllers;

use App\Models\CreatorPartner;
use App\Models\EventSponsorhip;
use App\Models\PadiUmkm;
use App\Models\RahasiaBisnis;
use App\Models\ReferralChampionAm;
use App\Models\RekruterKol;
use App\Models\SimpatiTiktok;
use App\Models\SultamRacing;
use Illuminate\Http\Request;
use App\Models\User;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


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

    public function getPadiUmkmData(Request $request)
    {
        if ($request->has('tanggal') && !empty($request->tanggal)) {
            $tanggal = $request->tanggal;
            $date = Carbon::parse($tanggal);
            $month = $date->month;
            $year = $date->year;
            $data = DB::table('summary_padi_umkm')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $data = DB::table('summary_padi_umkm')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return DataTables()->of($data)
            ->addIndexColumn()
            ->make(true);
    }
    public function getPadiUmkmSummary(Request $request)
    {
        $query = DB::table('summary_padi_umkm');

        if ($request->has('tanggal') && !empty($request->tanggal)) {
            $tanggal = $request->tanggal;
            $date = Carbon::parse($tanggal);
            $month = $date->month;
            $year = $date->year;
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        }

        $totalForm = $query->count();

        // Clone query for aggregation
        $topupQuery = clone $query;

        $jumlahTopup = $topupQuery->sum('jumlah_topup');
        $totalTopup = $topupQuery->sum('total_topup');

        return response()->json([
            'total_form'    => $totalForm,
            'jumlah_topup'  => $jumlahTopup,
            'total_topup'   => $totalTopup,
        ]);
    }
    public function getEventSponsorship(Request $request)
    {
        $data = EventSponsorhip::orderBy('created_at', 'desc')->get();

        return DataTables()->of($data)
            ->addIndexColumn()
            ->make(true);
    }


    public function getCreatorPartner(Request $request)
    {
        // Ambil semua creator + total_invited (subquery)
        $creators = CreatorPartner::select(
            'creator_partner.*',
            DB::raw('(SELECT COUNT(*) FROM rekruter_kol WHERE rekruter_kol.referral_code = creator_partner.referral_code) as total_invited')
        )
            ->when($request->area, function ($query, $area) {
                $query->where('creator_partner.area', $area);
            })
            ->when($request->region, function ($query, $region) {
                $query->where('creator_partner.regional', $region);
            })
            ->when($request->jenis_kol, function ($query, $jenis_kol) {
                $query->where('creator_partner.jenis_kol', $jenis_kol);
            })
            ->orderBy('creator_partner.created_at', 'desc')
            ->get();

        // Hitung tier untuk setiap creator partner
        $creators->transform(function ($creator) use ($request) {
            // ambil list email rekruter untuk referral ini
            $rekruterEmails = DB::table('rekruter_kol')
                ->where('referral_code', $creator->referral_code)
                ->pluck('email')
                ->toArray();

            if (empty($rekruterEmails)) {
                $creator->tier = '-';
            } else {
                // ambil total topup per email
                $topupPerAkun = DB::table('revenue_kol')
                    ->whereIn('email', $rekruterEmails)
                    ->select('email', DB::raw('SUM(jumlah_top_up) as total_topup'))
                    ->groupBy('email')
                    ->pluck('total_topup', 'email');

                // set minimum sesuai jenis_kol
                $minTopup = 0;
                if ($creator->jenis_kol === 'KOL as a Buzzer') {
                    $minTopup = 250000;
                } elseif ($creator->jenis_kol === 'KOL as a Seller Online/Afiliate') {
                    $minTopup = 200000;
                }

                // hitung berapa akun yang memenuhi minimum
                $eligibleAccounts = collect($topupPerAkun)->filter(function ($total) use ($minTopup) {
                    return $total >= $minTopup;
                })->count();

                // tentukan tier
                if ($eligibleAccounts >= 30) {
                    $creator->tier = 'Platinum';
                } elseif ($eligibleAccounts >= 20) {
                    $creator->tier = 'Gold';
                } elseif ($eligibleAccounts >= 10) {
                    $creator->tier = 'Silver';
                } elseif ($eligibleAccounts >= 5) {
                    $creator->tier = 'Bronze';
                } else {
                    $creator->tier = '-';
                }
            }

            // Filter berdasarkan tier (kalau user pilih filter tier)
            if ($request->tier && $creator->tier !== $request->tier) {
                $creator->hide = true; // kasih flag biar nanti dihapus
            }

            return $creator;
        });

        // Buang data yang tidak sesuai tier filter
        if ($request->tier) {
            $creators = $creators->reject(function ($creator) {
                return isset($creator->hide) && $creator->hide === true;
            });
        }

        // Kembalikan collection ke DataTables
        return DataTables::of($creators)->make(true);
    }
    public function getRekrutBuzzer(Request $request)
    {
        // Ambil referral_code semua creator dengan jenis KOL = Buzzer
        $buzzerReferralCodes = DB::table('creator_partner')
            ->where('jenis_kol', 'KOL as a Buzzer')
            ->pluck('referral_code');

        // Ambil semua rekruter yang referral_code-nya ada di list buzzer
        $rekrut = DB::table('rekruter_kol')
            ->whereIn('referral_code', $buzzerReferralCodes)
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform hasil biar sesuai table kamu
        $rekrut->transform(function ($item) {
            // Hitung total topup untuk email rekruter ini
            $totalTopup = DB::table('revenue_kol')
                ->where('email', $item->email)
                ->sum('jumlah_top_up');

            // Tentukan nilai minimal topup & remarks
            $minTopup = 250000; // karena jenis_kol = Buzzer
            $item->nilai_min_topup = $minTopup;
            $item->jumlah_top_up = $totalTopup;
            $item->remarks = $totalTopup >= $minTopup ? 'Eligible' : 'Not Eligible';

            return $item;
        });

        return DataTables::of($rekrut)
            ->addColumn('nilai_min_topup', function ($row) {
                return number_format($row->nilai_min_topup, 0, ',', '.');
            })
            ->addColumn('jumlah_top_up', function ($row) {
                return number_format($row->jumlah_top_up, 0, ',', '.');
            })
            ->addColumn('remarks', function ($row) {
                return $row->remarks;
            })
            ->make(true);
    }
    public function getRekruterInfluencer(Request $request)
    {
        // Ambil referral_code semua creator dengan jenis KOL = Influencer
        $influencerReferralCodes = DB::table('creator_partner')
            ->where('jenis_kol', 'KOL as a Seller Online/Afiliate')
            ->pluck('referral_code');

        // Ambil semua rekruter yang referral_code-nya ada di list influencer
        $rekrut = DB::table('rekruter_kol')
            ->whereIn('referral_code', $influencerReferralCodes)
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform hasil biar sesuai table kamu
        $rekrut->transform(function ($item) {
            // Hitung total topup untuk email rekruter ini
            $totalTopup = DB::table('revenue_kol')
                ->where('email', $item->email)
                ->sum('jumlah_top_up');

            // Tentukan nilai minimal topup & remarks
            $minTopup = 200000; // karena jenis_kol = Influencer
            $item->nilai_min_topup = $minTopup;
            $item->jumlah_top_up = $totalTopup;
            $item->remarks = $totalTopup >= $minTopup ? 'Eligible' : 'Not Eligible';

            return $item;
        });

        return DataTables::of($rekrut)
            ->addColumn('nilai_min_topup', function ($row) {
                return number_format($row->nilai_min_topup, 0, ',', '.');
            })
            ->addColumn('jumlah_top_up', function ($row) {
                return number_format($row->jumlah_top_up, 0, ',', '.');
            })
            ->addColumn('remarks', function ($row) {
                return $row->remarks;
            })
            ->make(true);
    }

    public function getAreaMarcom(Request $request)
    {
        $bulanIni = now()->format('Y-m'); // e.g. 2025-09

        // Ambil statistik per area (unikkan kol dengan COUNT DISTINCT)
        $areas = DB::table('creator_partner as cp')
            ->leftJoin('rekruter_kol as rk', DB::raw('cp.referral_code COLLATE utf8mb4_unicode_ci'), '=', DB::raw('rk.referral_code COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('revenue_kol as rv', DB::raw('rk.email COLLATE utf8mb4_unicode_ci'), '=', DB::raw('rv.email COLLATE utf8mb4_unicode_ci'))
            ->select(
                'cp.area',
                DB::raw("COUNT(DISTINCT cp.id) as total_kol"),
                DB::raw("SUM(CASE WHEN cp.jenis_kol = 'KOL as a Buzzer' THEN 1 ELSE 0 END) as jumlah_buzzer"),
                DB::raw("SUM(CASE WHEN cp.jenis_kol = 'KOL as a Seller Online/Afiliate' THEN 1 ELSE 0 END) as jumlah_influencer"),
                DB::raw("COUNT(DISTINCT rk.id) as total_rekruter"),
                DB::raw("COALESCE(SUM(rv.jumlah_top_up), 0) as total_topup"),
                // max topup dari revenue_kol yang terjadi pada bulan ini (untuk rule 3)
                DB::raw("MAX(CASE WHEN DATE_FORMAT(rv.created_at, '%Y-%m') = '{$bulanIni}' THEN rv.jumlah_top_up ELSE 0 END) as max_topup_bulan_ini")
            )
            ->groupBy('cp.area')
            ->orderBy('cp.area', 'asc')
            ->get();

        // Untuk setiap area: hitung bintang sesuai aturan:
        // 1) ada KOL -> 1 bintang
        // 2) ada minimal 1 creator di area yang punya >=5 akun rekruter eligible (eligible = akun punya total_topup >= minTopup tergantung jenis_kol)
        // 3) ada akun yang topup >= 1.000.000 pada bulan ini -> 1 bintang
        foreach ($areas as $areaRow) {
            $bintang = 0;

            // Rule 2: cek apakah ada creator di area yang sudah mencapai Tier >= Bronze
            // (Tier Bronze: ada >=5 rekruter dengan total_topup >= minTopup; minTopup berbeda per jenis_kol)
            $adaTierBronze = false;

            // Ambil semua creator di area (referral_code + jenis_kol)
            $creators = DB::table('creator_partner')
                ->where('area', $areaRow->area)
                ->select('referral_code', 'jenis_kol')
                ->get();

            foreach ($creators as $creator) {
                if (empty($creator->referral_code)) continue;

                // ambil list email rekruter untuk referral ini
                $emails = DB::table('rekruter_kol')
                    ->where('referral_code', $creator->referral_code)
                    ->pluck('email')
                    ->filter() // hapus null/empty
                    ->unique()
                    ->values()
                    ->toArray();

                if (empty($emails)) continue;

                // set minimum per jenis_kol
                $minTopup = $creator->jenis_kol === 'KOL as a Buzzer' ? 250000 : 200000;

                // ambil total topup per email (all-time)
                $topupPerEmail = DB::table('revenue_kol')
                    ->whereIn('email', $emails)
                    ->select('email', DB::raw('SUM(jumlah_top_up) as total'))
                    ->groupBy('email')
                    ->pluck('total')
                    ->toArray();

                // hitung berapa email yang memenuhi minTopup
                $eligibleCount = collect($topupPerEmail)->filter(fn($tot) => $tot >= $minTopup)->count();

                if ($eligibleCount >= 5) {
                    $adaTierBronze = true;
                    break; // cukup ada 1 creator yang memenuhi -> area dapat bintang tambahan
                }
            }

            if ($adaTierBronze) $bintang+=2;

            // Rule 3: ada akun rekruter yang topup >= 1jt pada bulan ini?
            if ((int)$areaRow->max_topup_bulan_ini >= 1000000) {
                $bintang++;
            }

            $areaRow->remarks = $bintang; // hanya kirim angka 0..3
        }

        return DataTables()->of($areas)
            ->addIndexColumn()
            ->make(true);
    }







    public function getSimpatiTiktok(Request $request)
    {
        $data = DB::table('summary_simpati_tiktok as sst')
            ->select(
                'sst.*'
            )
            ->orderBy('sst.created_at', 'desc')
            ->get();

        return DataTables()->of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function getReferralChampionAm(Request $request)
    {
        $data = ReferralChampionAm::orderBy('created_at', 'desc')->get();

        return DataTables()->of($data)
            ->addIndexColumn()
            ->make(true);
    }
    public function getSultamRacing(Request $request)
    {
        $data = SultamRacing::orderBy('created_at', 'desc')->get();

        return DataTables()->of($data)
            ->addIndexColumn()
            ->make(true);
    }
}
