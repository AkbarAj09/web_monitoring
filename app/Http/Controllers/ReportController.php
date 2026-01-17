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
            ->unique()
            ->filter(fn ($r) => $r !== 'UNKNOWN');

        foreach ($regions as $region) {
            $targetRow = $targets[$region] ?? null;
            $topupRow  = $topupPerRegion[$region] ?? null;

            $target = $targetRow ? (float) $targetRow->target_amount : 0;
            $pic    = $targetRow ? ($targetRow->pic ?? '-') : '-';
            $topup  = (float) ($topupRow->topup ?? 0);

            $percentage = $target > 0
                ? round(($topup / $target) * 100, 2)
                : 0;

            $data[] = [
                'region'     => $region,
                'pic'        => $pic,
                'target'     => $target,
                'topup'      => $topup,
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
        $data = DB::table('region_target as rt')
            ->leftJoin('mitra_sbp as ms', 'ms.regional', '=', 'rt.region_name')
            ->leftJoin('report_balance_top_up as rbt', 'rbt.email_client', '=', 'ms.email_myads')
            ->select(
                'rt.region_name',
                DB::raw('rt.target_amount'),
                DB::raw('COALESCE(SUM(rbt.total_settlement_klien), 0) as mitra_sbp'),
                DB::raw('ROUND(
                    (COALESCE(SUM(rbt.total_settlement_klien), 0) / rt.target_amount) * 100
                , 2) as ach_to_target')
            )
            ->groupBy(
                'rt.region_name',
                'rt.target_amount'
            )
            ->where('rt.data_type', '=', 'Mitra SBP')
            ->get();


        $grouped = $data->groupBy('area');

        return view('mitra-sbp.report-performance', compact('grouped', 'data'));
    }
    
}
