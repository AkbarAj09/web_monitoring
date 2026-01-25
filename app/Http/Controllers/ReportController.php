<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Topup;
use App\Models\LeadsMaster;

class ReportController extends Controller
{
     /* ================= PAGE LOAD ================= */
    public function topupCanvasser(Request $request)
    {
        logUserLogin();
        $month = $request->get('month', now()->format('Y-m'));

        $canvassers = DB::connection('mysql')
            ->table('users')
            ->where('role', 'cvsr')
            ->pluck('name');

        return view('report.topup-client-canvasser', [
            'month'      => $month,
            'canvassers' => $canvassers
        ]);
    }

    
public function topupCanvasserData(Request $request)
{
    /* ================= DATE RANGE ================= */
    if ($request->month) {
        [$year, $month] = explode('-', $request->month);
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = Carbon::create($year, $month, 1)->endOfMonth();
    } else {
        $start = $request->start
            ? Carbon::parse($request->start)->startOfDay()
            : now()->startOfMonth();

        $end = $request->end
            ? Carbon::parse($request->end)->endOfDay()
            : now()->endOfMonth();
    }

    /* ================= TOPUP ================= */
    $topups = DB::connection('pgsql')
        ->table('em_myads_topup')
        ->whereBetween('tgl_transaksi', [$start, $end])
        ->select('tgl_transaksi', 'email_client', 'total_settlement_klien')
        ->get()
        ->map(fn($t) => [
            'date'   => Carbon::parse($t->tgl_transaksi)->format('Y-m-d'),
            'email'  => strtolower(trim($t->email_client)),
            'amount' => (float) $t->total_settlement_klien,
        ]);

    if ($topups->isEmpty()) {
        return response()->json(['canvassers'=>[], 'rows'=>[]]);
    }

    $emails = $topups->pluck('email')->unique()->values();

    /* ================= FIX DUPLICATE EMAIL ================= */
    $sub = DB::connection('mysql')
        ->table('leads_master')
        ->selectRaw('LOWER(TRIM(email)) as email, MAX(id) as last_id')
        ->whereIn(DB::raw('LOWER(TRIM(email))'), $emails)
        ->groupBy(DB::raw('LOWER(TRIM(email))'));

    $master = DB::connection('mysql')
        ->table('leads_master as lm')
        ->joinSub($sub, 'x', function ($j) {
            $j->on('lm.id', '=', 'x.last_id');
        })
        ->join('users', 'users.id', '=', 'lm.user_id')
        ->where('users.role', 'cvsr');

    if ($request->canvassers) {
        $master->whereIn('users.name', $request->canvassers);
    }

    $master = $master
        ->selectRaw('LOWER(TRIM(lm.email)) as email, users.name as canvasser')
        ->get();

    if ($master->isEmpty()) {
        return response()->json(['canvassers'=>[], 'rows'=>[]]);
    }

    /* ================= MAP (EMAIL → 1 CANVASSER) ================= */
    $map = $master->pluck('canvasser', 'email');

    $canvassers = $map->values()->unique()->sort()->values();

    /* ================= BUILD PIVOT ================= */
    $rows = [];

    foreach ($topups as $t) {
        if (!isset($map[$t['email']])) continue;

        $c = $map[$t['email']];

        $rows[$t['date']][$c]['amount'] =
            ($rows[$t['date']][$c]['amount'] ?? 0) + $t['amount'];

        $rows[$t['date']][$c]['emails'][] = $t['email'];
    }

    return response()->json([
        'canvassers' => $canvassers,
        'rows' => $rows
    ]);
}

    public function getMoMTopup(Request $request)
    {
        // Ambil bulan sekarang dan bulan lalu (full bulan)
        $now = Carbon::now();

        // Periode bulan ini: mulai tgl 1 sampai akhir bulan sekarang
        $start1 = $now->copy()->startOfMonth()->startOfDay();
        $end1   = $now->copy()->endOfMonth()->endOfDay();

        // Periode bulan lalu: mulai tgl 1 sampai akhir bulan lalu
        $start2 = $now->copy()->subMonth()->startOfMonth()->startOfDay();
        $end2   = $now->copy()->subMonth()->endOfMonth()->endOfDay();

        // Query topup bulan ini
        $topupThisMonth = DB::connection('pgsql')->table('em_myads_topup')
            ->whereBetween('tgl_transaksi', [$start1, $end1])
            ->select('email_client', DB::raw('SUM(total_settlement_klien) as total'))
            ->groupBy('email_client')
            ->get()
            ->mapWithKeys(fn($row) => [strtolower(trim($row->email_client)) => (float) $row->total]);

        // Query topup bulan lalu
        $topupLastMonth = DB::connection('pgsql')->table('em_myads_topup')
            ->whereBetween('tgl_transaksi', [$start2, $end2])
            ->select('email_client', DB::raw('SUM(total_settlement_klien) as total'))
            ->groupBy('email_client')
            ->get()
            ->mapWithKeys(fn($row) => [strtolower(trim($row->email_client)) => (float) $row->total]);

        // Ambil mapping email -> canvasser dari database MySQL
        $emails = $topupThisMonth->keys()->merge($topupLastMonth->keys())->unique();

        $master = DB::connection('mysql')->table('leads_master')
            ->join('users', 'users.id', '=', 'leads_master.user_id')
            ->where('users.role', 'cvsr')
            ->whereIn(DB::raw('LOWER(TRIM(leads_master.email))'), $emails)
            ->selectRaw('LOWER(TRIM(leads_master.email)) as email, users.name as canvasser')
            ->get();

        $map = $master->pluck('canvasser', 'email');

        // Daftar canvasser unik
        $canvassers = $map->unique()->sort()->values();

        // Hitung total per canvasser
        $totalsThisMonth = [];
        $totalsLastMonth = [];

        foreach ($canvassers as $c) {
            $totalsThisMonth[$c] = 0;
            $totalsLastMonth[$c] = 0;
        }

        foreach ($topupThisMonth as $email => $total) {
            $c = $map[$email] ?? null;
            if ($c) $totalsThisMonth[$c] += $total;
        }

        foreach ($topupLastMonth as $email => $total) {
            $c = $map[$email] ?? null;
            if ($c) $totalsLastMonth[$c] += $total;
        }

        // Hitung selisih (bulan ini - bulan lalu)
        $selisih = [];
        foreach ($canvassers as $c) {
            $selisih[$c] = $totalsThisMonth[$c] - $totalsLastMonth[$c];
        }

        // Format response
        $response = [
            'canvassers' => $canvassers,
            'rows' => [
                'Total ' . $start2->format('M Y') => $totalsLastMonth,
                'Total ' . $start1->format('M Y') => $totalsThisMonth,
                'Selisih ' . $start1->format('M Y') . ' - ' . $start2->format('M Y') => $selisih,
            ]
        ];

        return response()->json($response);
    }

    /* ================= EXPORT EXCEL ================= */
    public function exportTopupCanvasserExcel(Request $request)
    {
        return Excel::download(
            new \App\Exports\TopupCanvasserExport($request),
            'topup-canvasser.xlsx'
        );
    }

    /* ================= EXPORT PDF ================= */
    public function exportTopupCanvasserPdf(Request $request)
    {
        $data = $this->topupCanvasserData($request)->getData(true);

        $pdf = Pdf::loadView('report.pdf.topup-canvasser', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('topup-canvasser.pdf');
    }

    public function reportRegionTargetVsTopup(Request $request)
    {
        logUserLogin();
        /* ================= FILTER BULAN ================= */
        $month = $request->get('month', now()->format('Y-m'));

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end   = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        /* ================= TOPUP PER REGION ================= */
        $topupPerRegion = DB::connection('mysql')
            ->table('report_balance_top_up as emt')
            ->selectRaw("
                CASE
                    WHEN data_province_name IN ('Sumatera Selatan','Jambi','Bengkulu','Lampung','Bangka Belitung', 'Kepulauan Bangka Belitung') THEN 'SUMBAGSEL'
                    WHEN data_province_name IN ('Sumatera Barat','Riau','Kepulauan Riau') THEN 'SUMBAGTENG'
                    WHEN data_province_name IN ('Sumatera Utara','Aceh') THEN 'SUMBAGUT'
                    WHEN data_province_name IN ('DKI Jakarta','Banten') THEN 'JABODETABEK'
                    WHEN data_province_name = 'Jawa Barat' THEN 'JABAR'
                    WHEN data_province_name IN ('Jawa Tengah','Yogyakarta', 'DI Yogyakarta') THEN 'JATENG DIY'
                    WHEN data_province_name = 'Jawa Timur' THEN 'JATIM'
                    WHEN data_province_name IN ('Bali','NTB','NTT') THEN 'BALI NUSRA'
                    WHEN data_province_name IN ('Kalimantan Tengah','Kalimantan Barat','Kalimantan Utara','Kalimantan Timur','Kalimantan Selatan') THEN 'KALIMANTAN'
                    WHEN data_province_name IN ('Sulawesi Utara','Sulawesi Tengah','Gorontalo','Sulawesi Tenggara','Sulawesi Selatan','Maluku Utara') THEN 'SULAWESI'
                    WHEN data_province_name IN ('Maluku','Papua Barat','Papua') THEN 'PAPUA MALUKU'
                    ELSE 'UNKNOWN'
                END AS region,
                SUM(emt.total_settlement_klien) AS topup
            ")
            ->whereBetween('emt.tgl_transaksi', [$start, $end])
            ->whereNotNull('emt.tgl_transaksi')
            // ->where('emt.payment_history_status', 'PAID')
            ->groupBy('region')
            ->get()
            ->mapWithKeys(fn ($item) => [strtoupper($item->region) => $item]);

        /* ================= TARGET ================= */
        $targets = DB::table('region_target')
            ->where('data_type', 'PowerHouse')
            ->whereMonth('date', $start->month)
            ->whereYear('date', $start->year)
            ->get()
            ->mapWithKeys(fn ($item) => [strtoupper($item->region_name) => $item]);

        /* ================= LAST UPDATE GLOBAL ================= */
        $lastUpdate = DB::connection('mysql')
            ->table('report_balance_top_up')
            ->whereBetween('tgl_transaksi', [$start, $end])
            ->whereNotNull('tgl_transaksi')
            ->where('payment_history_status', 'PAID')
            ->max('tgl_transaksi');

        /* ================= MERGE DATA ================= */
        $data = [];

        $regions = $targets->keys()
            ->merge($topupPerRegion->keys())
            ->unique();
            // ->filter(fn ($r) => $r !== 'UNKNOWN');

        // Hitung sisa hari di bulan berjalan
            $today = Carbon::now();
            $todayDate = $today->format('Y-m-d'); // Tanggal hari ini untuk filter transaksi
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::now()->endOfMonth(); // Untuk hitung sisa hari kerja
        // Daftar tanggal merah Indonesia 2026 (bisa disesuaikan atau query dari database)
        $holidays = [
            '2026-01-01', // Tahun Baru
            '2026-02-17', // Isra Miraj (estimasi)
            '2026-03-22', // Nyepi
            '2026-03-23', // Idul Fitri (estimasi)
            '2026-03-24', // Idul Fitri (estimasi)
            '2026-04-10', // Wafat Yesus Kristus
            '2026-05-01', // Hari Buruh
            '2026-05-02', // Cuti Bersama (estimasi)
            '2026-05-21', // Kenaikan Yesus Kristus
            '2026-05-30', // Idul Adha (estimasi)
            '2026-06-01', // Hari Pancasila
            '2026-06-20', // Tahun Baru Islam (estimasi)
            '2026-08-17', // Hari Kemerdekaan
            '2026-08-29', // Maulid Nabi (estimasi)
            '2026-12-25', // Hari Natal
        ];

        // Hitung hanya hari kerja (Senin-Jumat) yang tersisa, exclude weekend dan tanggal merah
        $remainingWorkingDays = 0;
        $currentDate = $today->copy();
        
        while ($currentDate->lte($endOfMonth)) {
            // Cek apakah hari ini adalah weekday (Senin-Jumat)
            $isWeekday = $currentDate->isWeekday(); // true jika Senin-Jumat
            
            // Cek apakah bukan tanggal merah
            $isNotHoliday = !in_array($currentDate->format('Y-m-d'), $holidays);
            
            // Jika weekday dan bukan tanggal merah, hitung sebagai hari kerja
            if ($isWeekday && $isNotHoliday) {
                $remainingWorkingDays++;
            }
            
            $currentDate->addDay();
        }
        
        foreach ($regions as $region) {
            $targetRow = $targets[$region] ?? null;
            $topupRow  = $topupPerRegion[$region] ?? null;

            $target = $targetRow ? (float) $targetRow->target_amount : 0;
            $pic    = $targetRow ? ($targetRow->pic ?? '-') : '-';
            $topup  = (float) ($topupRow->topup ?? 0);

            $percentage = $target > 0
                ? round(($topup / $target) * 100, 2)
                : 0;

            $gap = $topup - $target;
                
            $gapDaily = $remainingWorkingDays > 0 ? $gap / $remainingWorkingDays : 0;
            $gapDaily *= -1;

            if (strtoupper($region) === 'UNKNOWN') {
                $gap = 0;
                $gapDaily = 0;
            }

            $data[] = [
                'region'     => $region,
                'pic'        => $pic,
                'target'     => $target,
                'topup'      => $topup,
                'gap'        => $gap,
                'gap_daily'  => $gapDaily,
                'percentage' => $percentage,
            ];
        }

        return view('report.region-target-topup', [
            'data'       => collect($data)->values(),
            'lastUpdate' => $lastUpdate,
            'month'      => $month
        ]);
    }



    public function reportMitraSBP()
    {
        $data_mitra_sbp = DB::table('region_target as rt')
            ->leftJoin('mitra_sbp as ms', 'ms.regional', '=', 'rt.region_name')
            ->leftJoin('report_balance_top_up as rbt', function ($join) {
                $join->on('rbt.email_client', '=', 'ms.email_myads')
                    ->where('rbt.tgl_transaksi', '>=', Carbon::now()->startOfMonth());
            })
            ->select(
                'ms.area',
                'rt.region_name',
                'rt.target_amount',
                DB::raw('COALESCE(SUM(rbt.total_settlement_klien), 0) as mitra_sbp')
            )
            ->where('rt.data_type', 'Mitra SBP')
            ->where('ms.remark', 'Mitra SBP')
            ->groupBy(
                'ms.area',
                'rt.region_name',
                'rt.target_amount'
            )
            ->orderBy('ms.area')
            ->orderBy('rt.region_name')
            ->get();

        $grouped_mitra_sbp = $data_mitra_sbp->groupBy('area');

        $data_agency = DB::table('region_target as rt')
            ->leftJoin('mitra_sbp as ms', 'ms.regional', '=', 'rt.region_name')
            ->leftJoin('report_balance_top_up as rbt', function ($join) {
                $join->on('rbt.email_client', '=', 'ms.email_myads')
                    ->where('rbt.tgl_transaksi', '>=', Carbon::now()->startOfMonth());
            })
            ->select(
                'ms.area',
                'rt.region_name',
                'rt.target_amount',
                DB::raw('COALESCE(SUM(rbt.total_settlement_klien), 0) as agency')
            )
            ->where('rt.data_type', 'Agency')
            ->where('ms.remark', 'Agency')
            ->groupBy(
                'ms.area',
                'rt.region_name',
                'rt.target_amount'
            )
            ->orderBy('ms.area')
            ->orderBy('rt.region_name')
            ->get();

        $grouped_agency = $data_agency->groupBy('area');


        $data_internal = DB::table('region_target as rt')
            ->leftJoin('mitra_sbp as ms', 'ms.regional', '=', 'rt.region_name')
            ->leftJoin('report_balance_top_up as rbt', function ($join) {
                $join->on('rbt.email_client', '=', 'ms.email_myads')
                    ->where('rbt.tgl_transaksi', '>=', Carbon::now()->startOfMonth());
            })
            ->select(
                'ms.area',
                'rt.region_name',
                'rt.target_amount',
                DB::raw('COALESCE(SUM(rbt.total_settlement_klien), 0) as internal')
            )
            ->where('rt.data_type', 'Internal')
            ->where('ms.remark', 'Internal')
            ->groupBy(
                'ms.area',
                'rt.region_name',
                'rt.target_amount'
            )
            ->orderBy('ms.area')
            ->orderBy('rt.region_name')
            ->get();

        $grouped_internal = $data_internal->groupBy('area');
        return view('mitra-sbp.report-performance', compact('grouped_mitra_sbp', 'data_mitra_sbp','grouped_agency', 'data_agency','grouped_internal', 'data_internal'));
    }

    
}
