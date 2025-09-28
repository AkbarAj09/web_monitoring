<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Log;

class FrontController extends Controller
{
    public function index()
    {
        if (Auth::user() or 'sukseslogin' == Session::get('login')) {
            if ('User' == Auth::user()->role) {
                return redirect('/home');
            } else if ('TL' == Auth::user()->role) {
                return redirect('/loglogin');
            } else {
                return redirect('/admin/home');
            }
            return view('user.index');
        }
        // return view('errors.503');
        return view('auth.login');
    }
    public function register()
    {
        return view('auth.register');
    }
    public function homeAdmin()
    {
        return view('admin.home');
    }
    public function logout()
    {
        // Menghapus sesi dan logout
        Session::flush();
        Auth::logout();
        // Redirect ke halaman utama
        return redirect('/');
    }
    public function uploadMyAds()
    {
        $myAdsUploads = [
            "Top Up Naik, Cuan Naik",
            "Top Up Ceria",
            "Revenue Top Up",
            "Grup Manajemen User Klien"
        ];
        return view('admin.upload', compact('myAdsUploads'));
    }
    public function monitoring_padi_umkm()
    {
        $months = [];

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->format('Y-m-01'); // bulan sekarang, tanggal 01

        for ($i = 1; $i <= 12; ++$i) {
            $date = Carbon::create($currentYear, $i, 1);
            $months[] = [
                'value' => $date->format('Y-m-d'), // e.g., 2025-05-01
                'label' => $date->translatedFormat('F Y'), // e.g., Mei 2025
                'selected' => $date->format('Y-m-d') === $currentMonth,
            ];
        }
        return view('admin.monitoring_padi_umkm', compact('months'));
    }
    public function monitoringEventSponsorship()
    {
        $months = [];

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->format('Y-m-01'); // bulan sekarang, tanggal 01

        for ($i = 1; $i <= 12; ++$i) {
            $date = Carbon::create($currentYear, $i, 1);
            $months[] = [
                'value' => $date->format('Y-m-d'), // e.g., 2025-05-01
                'label' => $date->translatedFormat('F Y'), // e.g., Mei 2025
                'selected' => $date->format('Y-m-d') === $currentMonth,
            ];
        }
        return view('admin.event_sponsorship', compact('months'));
    }
    public function monitoringCreatorPartner()
    {
        return view('admin.creator_partner');
    }
    public function monitoringSimpatiTiktok()
    {
        return view('admin.simpati_tiktok');
    }
    public function monitoringReferralChampionAm()
    {
        return view('admin.referral_champion');
    }
    public function monitoringReferralChampionTeleAm()
    {
        return view('admin.referral_tele_am');
    }
    public function monitoringReferralChampionCanvasser()
    {
        return view('admin.referral_canvasser');
    }
    public function monitoringSultamRacing()
    {
        return view('admin.sultam_racing');
    }
    public function refreshSummarySimpatiTiktok()
    {
        $data = DB::table('simpati_tiktok as st')
            ->select(
                'st.email',
                DB::raw('MAX(st.id) as simpati_tiktok_id'), // ambil id terakhir utk referensi
                DB::raw('MAX(st.no_hp) as no_hp'),
                DB::raw('MAX(st.nama_lengkap) as nama_lengkap'),
                DB::raw('COUNT(r.id) as jumlah_topup'),
                DB::raw('COALESCE(SUM(r.saldo_utama), 0) as total_saldo_utama'),
                DB::raw('COALESCE(SUM(r.saldo_bonus), 0) as total_saldo_bonus'),
                DB::raw('COALESCE(SUM(r.total), 0) as total_topup'),
                DB::raw('MIN(st.created_at) as created_at'),
                DB::raw('MAX(st.updated_at) as updated_at')
            )
            ->leftJoin('manajemen_user_register as mur', DB::raw('st.email COLLATE utf8mb4_unicode_ci'), '=', DB::raw('mur.email COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('revenue as r', DB::raw('mur.reg_id COLLATE utf8mb4_unicode_ci'), '=', DB::raw('r.id_klien COLLATE utf8mb4_unicode_ci'))
            ->where('r.status', 'PAID')
            ->groupBy('st.email')
            ->get();

        $totalData = count($data);
        $inserted = 0;

        foreach ($data as $row) {
            $result = DB::table('summary_simpati_tiktok')->updateOrInsert(
                ['email' => $row->email], // pakai email sbg unique key
                [
                    'simpati_tiktok_id' => $row->simpati_tiktok_id,
                    'no_hp'         => $row->no_hp,
                    'nama_lengkap'  => $row->nama_lengkap,
                    'jumlah_topup'  => $row->jumlah_topup,
                    'total_saldo_utama' => $row->total_saldo_utama,
                    'total_saldo_bonus' => $row->total_saldo_bonus,
                    'total_topup'   => $row->total_topup,
                    'created_at'    => $row->created_at,
                    'updated_at'    => now(),
                ]
            );
            // updateOrInsert tidak return affected rows, jadi kita anggap semua proses sukses
            $inserted++;
        }

        // Logging ke channel simpatiTiktok
        Log::channel('simpatiTiktok')->info('refreshSummarySimpatiTiktok executed', [
            'total_data_from_query' => $totalData,
            'total_processed' => $inserted,
            'timestamp' => now()->toDateTimeString()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Summary Simpati Tiktok by email updated. Total data: $totalData, processed: $inserted"
        ]);
    }

    public function refreshSummaryPadiUmkm()
    {
        $data = DB::table('padi_umkm as pd')
            ->select(
                'pd.email',
                DB::raw('MAX(pd.id) as padi_umkm_id'),
                DB::raw('MAX(pd.no_hp) as no_hp'),
                DB::raw('MAX(pd.nama) as nama'),
                DB::raw('MAX(pd.nama_usaha) as nama_usaha'),
                DB::raw('COUNT(r.id) as jumlah_topup'),
                DB::raw('COALESCE(SUM(r.total), 0) as total_topup'),
                DB::raw('MIN(pd.created_at) as created_at'),
                DB::raw('MAX(pd.updated_at) as updated_at')
            )
            ->leftJoin('manajemen_user_register as mur', DB::raw('pd.email COLLATE utf8mb4_unicode_ci'), '=', DB::raw('mur.email COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('revenue as r', DB::raw('mur.reg_id COLLATE utf8mb4_unicode_ci'), '=', DB::raw('r.id_klien COLLATE utf8mb4_unicode_ci'))
            ->groupBy('pd.email')
            ->get();

        foreach ($data as $row) {
            DB::table('summary_padi_umkm')->updateOrInsert(
                ['email' => $row->email],
                [
                    'padi_umkm_id' => $row->padi_umkm_id,
                    'no_hp'        => $row->no_hp,
                    'nama'         => $row->nama,
                    'nama_usaha'   => $row->nama_usaha,
                    'jumlah_topup' => $row->jumlah_topup,
                    'total_topup'  => $row->total_topup,
                    'created_at'   => $row->created_at,
                    'updated_at'   => now(),
                ]
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Summary Padi UMKM by email updated']);
    }
    public function getRegionals(Request $request)
    {
        $query = DB::table('creator_partner')
            ->select('regional')
            ->distinct();

        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }

        $regionals = $query->orderBy('regional')->pluck('regional');

        return response()->json($regionals);
    }
    public function rekruterKolBuzzer()
    {
        return view('admin.kol_buzzer');
    }
    public function rekruterKolInfluencer()
    {
        return view('admin.kol_influencer');
    }
    public function areaMarkomKol()
    {
        return view('admin.area_marcom');
    }
}
