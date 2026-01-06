<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Topup;
use App\Models\LeadsMaster;

class ReportController extends Controller
{
    public function topupCanvasser(Request $request)
    {
        /* -------------------------------------------------
        | 1. Date range
        ------------------------------------------------- */
        $start = $request->start
            ? Carbon::parse($request->start)->startOfDay()
            : now()->subMonth()->startOfMonth();

        $end = $request->end
            ? Carbon::parse($request->end)->endOfDay()
            : now()->endOfMonth();

        /* -------------------------------------------------
        | 2. Get TOPUP data (PostgreSQL) – LEFT table
        ------------------------------------------------- */
        $topups = DB::connection('pgsql')
            ->table('em_myads_topup')
            ->whereBetween('tgl_transaksi', [$start, $end])
            ->select('tgl_transaksi', 'email_client', 'total_settlement_klien')
            ->orderBy('tgl_transaksi')
            ->get()
            ->map(function ($t) {
                $t->email = strtolower(trim($t->email_client));
                $t->amount = (float) $t->total_settlement_klien;
                $t->date = Carbon::parse($t->tgl_transaksi)->format('Y-m-d');
                return $t;
            });

        if ($topups->isEmpty()) {
            return view('report.topup-client-canvasser', [
                'canvassers' => [],
                'data' => [],
            ]);
        }

        /* -------------------------------------------------
        | 3. Collect unique emails (join key)
        ------------------------------------------------- */
        $emails = $topups->pluck('email')->unique()->values();

        /* -------------------------------------------------
        | 4. LEFT JOIN – leads_master (MySQL)
        ------------------------------------------------- */
        $masterCvs = DB::connection('mysql')
            ->table('leads_master')
            ->join('users', 'users.id', '=', 'leads_master.user_id')
            ->where('users.role', 'Canvasser')
            ->whereIn(DB::raw('LOWER(TRIM(leads_master.email))'), $emails)
            ->whereNotNull('users.name')
            ->where('users.name', '!=', '')
            ->selectRaw('LOWER(TRIM(leads_master.email)) as email, users.name as canvasser')
            ->pluck('canvasser', 'email');
        // dd($masterCvs);
        // dd($masterCvs);
        /* -------------------------------------------------
        | 5. Application-level LEFT JOIN processing
        ------------------------------------------------- */
        $rows = [];
        $dateEmails = [];
        $canvassersSet = collect();

        foreach ($topups as $topup) {
            $email = $topup->email;

            // Only use leads_master for mapping
            $canvasser = $masterCvs[$email] ?? null;

            // Behaves like LEFT JOIN → unmatched rows still exist
            if (!$canvasser) {
                continue;
            }

            $canvassersSet->push($canvasser);
            $dateEmails[$topup->date][] = $email;

            $rows[$topup->date][$canvasser]['total_amount'] =
                ($rows[$topup->date][$canvasser]['total_amount'] ?? 0) + $topup->amount;

            $rows[$topup->date][$canvasser]['emails'][] = $email;
        }
        // dd($rows);
        if (empty($rows)) {
            return view('report.topup-client-canvasser', [
                'canvassers' => [],
                'data' => [],
            ]);
        }

        /* -------------------------------------------------
        | 6. Prepare frontend table data
        ------------------------------------------------- */
        $dates = collect(array_keys($rows))->sort()->values();
        $canvassers = $canvassersSet->unique()->sort()->values();

        $data = [];

        foreach ($dates as $date) {
            $row = [
                'tanggal' => Carbon::parse($date)->format('d M Y'),
                'total_amount' => 0,
                'total_email' => collect($dateEmails[$date] ?? [])
                    ->unique()
                    ->count(),
            ];

            foreach ($canvassers as $c) {
                $amount = (float) ($rows[$date][$c]['total_amount'] ?? 0);
                $emails = collect($rows[$date][$c]['emails'] ?? [])
                    ->unique()
                    ->count();

                $row[$c . '_amount'] = $amount > 0 ? $amount : '-';
                $row[$c . '_email'] = $emails > 0 ? $emails : '-';

                $row['total_amount'] += $amount;
            }

            $data[] = $row;
        }

        return view('report.topup-client-canvasser', [
            'canvassers' => $canvassers,
            'data' => $data,
        ]);
    }

    public function reportRegionTargetVsTopup()
    {
        // Dynamic current month
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        /* -----------------------------------------
        | 1. TOPUP PER REGION (PGSQL)
        ----------------------------------------- */
        $topupPerRegion = DB::connection('pgsql')
            ->table('em_myads_topup as emt')
            ->selectRaw("
                CASE
                    WHEN data_province_name IN ('Sumatera Selatan','Jambi','Bengkulu','Lampung','Bangka Belitung') THEN 'SUMBAGSEL'
                    WHEN data_province_name IN ('Sumatera Barat','Riau','Kepulauan Riau') THEN 'SUMBAGTENG'
                    WHEN data_province_name IN ('Sumatera Utara','Aceh') THEN 'SUMBAGUT'
                    WHEN data_province_name IN ('DKI Jakarta','Banten') THEN 'JABODETABEK'
                    WHEN data_province_name = 'Jawa Barat' THEN 'JABAR'
                    WHEN data_province_name IN ('Jawa Tengah','Yogyakarta') THEN 'JATENG DIY'
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
            ->where('emt.payment_history_status', 'PAID')
            ->groupBy('region')
            ->get()
            ->mapWithKeys(fn($item) => [strtoupper($item->region) => $item]);;

        /* -----------------------------------------
        | 2. TARGET CURRENT MONTH
        ----------------------------------------- */
        $targets = DB::table('region_target')
            ->whereMonth('date', $start->month)
            ->whereYear('date', $start->year)      
            ->get() // get full rows
            ->mapWithKeys(fn($item) => [strtoupper($item->region_name) => $item]);

        /* -----------------------------------------
        | 3. MERGE + CALCULATE PERCENTAGE
        ----------------------------------------- */
        $data = [];

        $regions = $targets->keys()
            ->merge($topupPerRegion->keys())
            ->unique()
            ->filter(fn($region) => $region !== 'UNKNOWN');

        foreach ($regions as $region) {
            $targetRow = $targets[$region] ?? null;

            $target = $targetRow ? (float) $targetRow->target_amount : 0;
            $pic    = $targetRow ? ($targetRow->pic ?? '-') : '-';
            $topup  = (float) ($topupPerRegion[$region]->topup ?? 0);

            $percentage = $target > 0 ? round(($topup / $target) * 100, 2) : 0;

            $data[] = [
                'region'     => $region,
                'pic'        => $pic,
                'target'     => $target,
                'topup'      => $topup,
                'percentage' => $percentage,
            ];
        }

        // Return to Blade view
        return view('report.region-target-topup', [
            'data' => collect($data)->sortBy('id')->values()
        ]);
    }

}
