<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeadProgramController extends Controller
{
    public function dashboard()
    {
        try {
            // Ambil data bulan berjalan
            $dailyData = $this->getDailyTopupData();
            
            return view('admin.home', compact('dailyData'));
        } catch (\Exception $e) {
            return view('admin.home', [
                'dailyData' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getDailyTopupData()
    {
        try {
            // Ambil email dari masing-masing kategori
            $mitraSbpEmails = DB::table('mitra_sbp')
                ->where('remark', 'Mitra SBP')
                ->pluck('email_myads')
                ->toArray();

            $agencyEmails = DB::table('mitra_sbp')
                ->where('remark', 'Agency')
                ->pluck('email_myads')
                ->toArray();

            $outletEmails = DB::table('mitra_sbp')
                ->where('remark', 'Outlet')
                ->pluck('email_myads')
                ->toArray();

            $canvasserEmails = DB::table('leads_master')
                ->pluck('email')
                ->toArray();

            // Query data topup dari MySQL untuk bulan berjalan
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            
            // Buat query dengan raw SQL untuk debugging
            $query = "SELECT 
                DATE(tgl_transaksi) as tanggal,
                email_client AS email,
                user_id AS id_user,
                CAST(total_settlement_klien AS DECIMAL(15,2)) as total_settlement
                FROM report_balance_top_up
                WHERE tgl_transaksi >= '{$startDate}'
                AND email_client IS NOT NULL
                AND user_id IS NOT NULL
                AND total_settlement_klien IS NOT NULL
                ORDER BY tgl_transaksi DESC";
                
            
            $topupData = DB::table('report_balance_top_up as rp')
                ->leftJoin('mitra_sbp as m', 'm.email_myads', '=', 'rp.email_client')
                ->leftJoin('leads_master as lm', 'lm.email', '=', 'rp.email_client')
                ->select(
                    DB::raw("DATE(rp.tgl_transaksi) as tanggal"),
                    'rp.email_client as email',
                    'rp.user_id as id_user',
                    DB::raw("CAST(rp.total_settlement_klien AS DECIMAL(15,2)) as total_settlement"),
                    'm.remark',
                    'lm.source_id',
                    'rp.payment_method_name',
                    'rp.company_name'
                )
                ->whereRaw("rp.tgl_transaksi >= ?", [$startDate])
                ->whereNotNull('rp.email_client')
                ->whereNotNull('rp.user_id')
                ->whereNotNull('rp.total_settlement_klien')
                ->orderBy('rp.tgl_transaksi', 'desc')
                ->get();
            
            if ($topupData->count() === 0) {
                return [];
            }

        // Group by tanggal dan kategorikan
        $groupedData = [];
        
        foreach ($topupData as $row) {
            $date = $row->tanggal;
            
            if (!isset($groupedData[$date])) {
                $groupedData[$date] = [
                    'mitra_sbp' => ['settlement' => 0, 'users' => []],
                    'agency' => ['settlement' => 0, 'users' => []],
                    'outlet' => ['settlement' => 0, 'users' => []],
                    'canvasser' => ['settlement' => 0, 'users' => []],
                ];
            }

            $email = strtolower(trim($row->email));
            $settlement = floatval($row->total_settlement);
            $userId = $row->id_user;

            // Kategorikan berdasarkan email terlebih dahulu (logic lama)
            if (in_array($email, array_map('strtolower', $mitraSbpEmails))) {
                $groupedData[$date]['mitra_sbp']['settlement'] += $settlement;
                if (!in_array($userId, $groupedData[$date]['mitra_sbp']['users'])) {
                    $groupedData[$date]['mitra_sbp']['users'][] = $userId;
                }
            } elseif (in_array($email, array_map('strtolower', $agencyEmails))) {
                $groupedData[$date]['agency']['settlement'] += $settlement;
                if (!in_array($userId, $groupedData[$date]['agency']['users'])) {
                    $groupedData[$date]['agency']['users'][] = $userId;
                }
            } elseif (in_array($email, array_map('strtolower', $canvasserEmails))) {
                $groupedData[$date]['canvasser']['settlement'] += $settlement;
                if (!in_array($userId, $groupedData[$date]['canvasser']['users'])) {
                    $groupedData[$date]['canvasser']['users'][] = $userId;
                }
            } else {
                // Untuk yang tidak termasuk 3 kategori di atas, gunakan logic baru untuk Self Service/Outlet
                $category = 'Self Service';
                
                // 1. Cek remark dari mitra_sbp
                if (!empty($row->remark) && $row->remark == 'Outlet') {
                    $category = 'Outlet';
                }
                // 2. Jika remark null, cek source_id dari leads_master
                elseif (isset($row->source_id) && $row->source_id == 0) {
                    // 3. Cek payment_method_name
                    if (!in_array($row->payment_method_name, ['MyTelkomsel', 'DigiPOS'])) {
                        $category = 'Self Service';
                    }
                }
                
                // 4. Cek company_name untuk Outlet
                if ($category == 'Self Service' && !empty($row->company_name)) {
                    if (preg_match('/cell/i', $row->company_name)) {
                        $category = 'Outlet';
                    }
                }
                
                // Masukkan ke outlet (Self Service/Outlet digabung)
                $groupedData[$date]['outlet']['settlement'] += $settlement;
                if (!in_array($userId, $groupedData[$date]['outlet']['users'])) {
                    $groupedData[$date]['outlet']['users'][] = $userId;
                }
            }
        }

        // Format hasil untuk view
        $result = [];
        $totals = [
            'mitra_sbp_settle' => 0,
            'mitra_sbp_user' => [],
            'agency_settle' => 0,
            'agency_user' => [],
            'outlet_settle' => 0,
            'outlet_user' => [],
            'canvasser_settle' => 0,
            'canvasser_user' => [],
        ];

        // Sort by date descending
        krsort($groupedData);

        foreach ($groupedData as $date => $data) {
            $row = [
                'date' => Carbon::parse($date)->locale('id')->translatedFormat('d F Y'),
                'mitra_sbp_settle' => number_format($data['mitra_sbp']['settlement'], 0, ',', '.'),
                'mitra_sbp_user' => count($data['mitra_sbp']['users']),
                'agency_settle' => number_format($data['agency']['settlement'], 0, ',', '.'),
                'agency_user' => count($data['agency']['users']),
                'self_service_settle' => number_format($data['outlet']['settlement'], 0, ',', '.'),
                'self_service_user' => count($data['outlet']['users']),
                'canvasser_settle' => number_format($data['canvasser']['settlement'], 0, ',', '.'),
                'canvasser_user' => count($data['canvasser']['users']),
                'total' => number_format(
                    $data['mitra_sbp']['settlement'] + 
                    $data['agency']['settlement'] + 
                    $data['outlet']['settlement'] + 
                    $data['canvasser']['settlement'], 
                    0, ',', '.'
                ),
                'total_user' => count(array_unique(array_merge(
                    $data['mitra_sbp']['users'],
                    $data['agency']['users'],
                    $data['outlet']['users'],
                    $data['canvasser']['users']
                ))),
            ];

            $result[] = $row;

            // Tambahkan ke total keseluruhan
            $totals['mitra_sbp_settle'] += $data['mitra_sbp']['settlement'];
            $totals['mitra_sbp_user'] = array_unique(array_merge($totals['mitra_sbp_user'], $data['mitra_sbp']['users']));
            $totals['agency_settle'] += $data['agency']['settlement'];
            $totals['agency_user'] = array_unique(array_merge($totals['agency_user'], $data['agency']['users']));
            $totals['outlet_settle'] += $data['outlet']['settlement'];
            $totals['outlet_user'] = array_unique(array_merge($totals['outlet_user'], $data['outlet']['users']));
            $totals['canvasser_settle'] += $data['canvasser']['settlement'];
            $totals['canvasser_user'] = array_unique(array_merge($totals['canvasser_user'], $data['canvasser']['users']));
        }

        // Tambahkan row total
        if (!empty($result)) {
            $result[] = [
                'date' => 'Total Keseluruhan',
                'mitra_sbp_settle' => number_format($totals['mitra_sbp_settle'], 0, ',', '.'),
                'mitra_sbp_user' => count($totals['mitra_sbp_user']),
                'agency_settle' => number_format($totals['agency_settle'], 0, ',', '.'),
                'agency_user' => count($totals['agency_user']),
                'self_service_settle' => number_format($totals['outlet_settle'], 0, ',', '.'),
                'self_service_user' => count($totals['outlet_user']),
                'canvasser_settle' => number_format($totals['canvasser_settle'], 0, ',', '.'),
                'canvasser_user' => count($totals['canvasser_user']),
                'total' => number_format(
                    $totals['mitra_sbp_settle'] + 
                    $totals['agency_settle'] + 
                    $totals['outlet_settle'] + 
                    $totals['canvasser_settle'], 
                    0, ',', '.'
                ),
                'total_user' => count(array_unique(array_merge(
                    $totals['mitra_sbp_user'],
                    $totals['agency_user'],
                    $totals['outlet_user'],
                    $totals['canvasser_user']
                ))),
            ];
        }

        return $result;
        } catch (\Exception $e) {
            \Log::error("Error in getDailyTopupData: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return [];
        }
    }

    public function getDailyTopupDataTable(Request $request)
    {
        try {
            $result = $this->getDailyTopupData();
            
            return datatables()->of(collect($result))
                ->make(true);

        } catch (\Exception $e) {
            \Log::error("Error in getDailyTopupDataTable: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getLeadsAndAccountData()
    {
        try {
            // Tanggal 1 bulan yang lalu
            $oneMonthAgo = Carbon::now()->subMonth()->format('Y-m-d');
            
            // 1. Jumlah Leads dari leads_master dengan data_type = 'leads'
            $totalLeads = DB::table('leads_master')
                ->where('data_type', 'leads')
                ->count();
            
            // 2. Query untuk mendapatkan data akun existing dan new
            $accountData = DB::table('data_registarsi_status_approveorreject as dt')
                ->join('leads_master as lm', 'dt.email', '=', 'lm.email')
                ->join('users as u', 'lm.user_id', '=', 'u.id')
                ->join('report_balance_top_up as rp', 'dt.email', '=', 'rp.email_client')
                ->leftJoin('regional_tracers as rt', 'lm.email', '=', 'rt.pic_email')
                ->select(
                    'u.name',
                    'dt.email as email_register',
                    'dt.tanggal_approval_aktivasi',
                    DB::raw('CAST(rp.total_settlement_klien AS DECIMAL(15,2)) as total_settlement_klien'),
                    'rt.regional',
                    DB::raw("CASE
                        WHEN STR_TO_DATE(dt.tanggal_approval_aktivasi, '%Y-%m-%d') <= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                        THEN 'akun_existing'
                        ELSE 'akun_new'
                    END AS status_akun")
                )
                ->get();
            
            // Hitung jumlah existing akun
            $existingAkun = $accountData->where('status_akun', 'akun_existing')->unique('email_register')->count();
            
            // Hitung jumlah new akun
            $newAkun = $accountData->where('status_akun', 'akun_new')->unique('email_register')->count();
            
            // Hitung total top up new akun
            $topUpNewAkun = $accountData->where('status_akun', 'akun_new')
                ->sum('total_settlement_klien');
            
            // Hitung total top up existing akun
            $topUpExistingAkun = $accountData->where('status_akun', 'akun_existing')
                ->sum('total_settlement_klien');
            
            return [
                'total_leads' => $totalLeads,
                'existing_akun' => $existingAkun,
                'new_akun' => $newAkun,
                'top_up_new_akun' => $topUpNewAkun,
                'top_up_existing_akun' => $topUpExistingAkun,
            ];

        } catch (\Exception $e) {
            \Log::error("Error in getLeadsAndAccountData: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return [
                'total_leads' => 0,
                'existing_akun' => 0,
                'new_akun' => 0,
                'top_up_new_akun' => 0,
                'top_up_existing_akun' => 0,
            ];
        }
    }

    public function getLeadsDataApi()
    {
        try {
            $data = $this->getLeadsAndAccountData();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getRegionalData()
    {
        try {
            // 1. Ambil semua user dengan role 'Canvasser' dari kolom role di tabel users
            $canvasers = DB::table('users')
                ->where('role', 'canvasser')
                ->select('id', 'name')
                ->get();

            \Log::info("Total Canvassers found: " . $canvasers->count());

            $result = [];
            
            foreach ($canvasers as $index => $canvaser) {
                // 2. Ambil regional dari leads_master untuk canvaser ini
                $regional = DB::table('leads_master as lm')
                    ->leftJoin('regional_tracers as rt', 'lm.email', '=', 'rt.pic_email')
                    ->where('lm.user_id', $canvaser->id)
                    ->select('rt.regional')
                    ->first();

                // 3. Hitung jumlah Leads untuk canvaser ini
                $totalLeads = DB::table('leads_master')
                    ->where('user_id', $canvaser->id)
                    ->where('data_type', 'leads')
                    ->count();

                // 4. Hitung Existing Akun dan New Akun menggunakan join langsung dengan leads_master
                $accountStats = DB::table('data_registarsi_status_approveorreject as dt')
                    ->join('leads_master as lm', 'dt.email', '=', 'lm.email')
                    ->where('lm.user_id', $canvaser->id)
                    ->select(
                        DB::raw("COUNT(DISTINCT CASE 
                            WHEN STR_TO_DATE(dt.tanggal_approval_aktivasi, '%Y-%m-%d') <= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                            THEN dt.email 
                        END) as existing_akun"),
                        DB::raw("COUNT(DISTINCT CASE 
                            WHEN STR_TO_DATE(dt.tanggal_approval_aktivasi, '%Y-%m-%d') > DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                            THEN dt.email 
                        END) as new_akun")
                    )
                    ->first();

                // 5. Hitung Top Up dari report_balance_top_up menggunakan join langsung
                $topUpStats = DB::table('data_registarsi_status_approveorreject as dt')
                    ->join('leads_master as lm', 'dt.email', '=', 'lm.email')
                    ->join('report_balance_top_up as rp', 'dt.email', '=', 'rp.email_client')
                    ->where('lm.user_id', $canvaser->id)
                    ->select(
                        DB::raw("COUNT(CASE 
                            WHEN STR_TO_DATE(dt.tanggal_approval_aktivasi, '%Y-%m-%d') > DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                            THEN rp.id 
                        END) as top_up_count"),
                        DB::raw("SUM(CASE 
                            WHEN STR_TO_DATE(dt.tanggal_approval_aktivasi, '%Y-%m-%d') > DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                            THEN CAST(rp.total_settlement_klien AS DECIMAL(15,2))
                            ELSE 0 
                        END) as top_up_new_akun_rp"),
                        DB::raw("SUM(CASE 
                            WHEN STR_TO_DATE(dt.tanggal_approval_aktivasi, '%Y-%m-%d') <= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                            THEN CAST(rp.total_settlement_klien AS DECIMAL(15,2))
                            ELSE 0 
                        END) as top_up_existing_akun_rp")
                    )
                    ->first();

                $result[] = [
                    'no' => $index + 1,
                    'regional' => $regional->regional ?? '-',
                    'canvaser_name' => $canvaser->name ?? '-',
                    'leads' => $totalLeads,
                    'existing_akun' => $accountStats->existing_akun ?? 0,
                    'new_akun' => $accountStats->new_akun ?? 0,
                    'top_up' => $topUpStats->top_up_count ?? 0,
                    'top_up_new_akun_rp' => number_format($topUpStats->top_up_new_akun_rp ?? 0, 0, ',', '.'),
                    'top_up_existing_akun_rp' => number_format($topUpStats->top_up_existing_akun_rp ?? 0, 0, ',', '.'),
                ];
            }

            \Log::info("Total results: " . count($result));
            return $result;

        } catch (\Exception $e) {
            \Log::error("Error in getRegionalData: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return [];
        }
    }

    public function getRegionalDataTable(Request $request)
    {
        try {
            $result = $this->getRegionalData();
            
            return datatables()->of(collect($result))
                ->make(true);

        } catch (\Exception $e) {
            \Log::error("Error in getRegionalDataTable: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getRegionalChartData()
    {
        try {
            // Agregasi langsung dari database untuk performa lebih baik
            $canvasers = DB::table('users')
                ->where('role', 'cvsr')
                ->pluck('id');

            if ($canvasers->isEmpty()) {
                return response()->json([
                    'total_leads' => 0,
                    'total_existing_akun' => 0,
                    'total_new_akun' => 0,
                    'total_top_up_new_akun' => 0,
                    'total_top_up_existing_akun' => 0,
                ]);
            }

            // Total Leads
            $totalLeads = DB::table('leads_master')
                ->whereIn('user_id', $canvasers)
                ->where('data_type', 'leads')
                ->count();

            // Total Existing Akun dan New Akun
            $accountStats = DB::table('data_registarsi_status_approveorreject as dt')
                ->join('leads_master as lm', 'dt.email', '=', 'lm.email')
                ->whereIn('lm.user_id', $canvasers)
                ->select(
                    DB::raw("COUNT(DISTINCT CASE 
                        WHEN STR_TO_DATE(dt.tanggal_approval_aktivasi, '%Y-%m-%d') <= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                        THEN dt.email 
                    END) as existing_akun"),
                    DB::raw("COUNT(DISTINCT CASE 
                        WHEN STR_TO_DATE(dt.tanggal_approval_aktivasi, '%Y-%m-%d') > DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                        THEN dt.email 
                    END) as new_akun")
                )
                ->first();

            // Total Top Up untuk New Akun dan Existing Akun (COUNT)
            $topUpStats = DB::table('data_registarsi_status_approveorreject as dt')
                ->join('leads_master as lm', 'dt.email', '=', 'lm.email')
                ->join('report_balance_top_up as rp', 'dt.email', '=', 'rp.email_client')
                ->whereIn('lm.user_id', $canvasers)
                ->select(
                    DB::raw("COUNT(DISTINCT CASE 
                        WHEN STR_TO_DATE(dt.tanggal_approval_aktivasi, '%Y-%m-%d') > DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                        THEN rp.id 
                    END) as top_up_new_akun"),
                    DB::raw("COUNT(DISTINCT CASE 
                        WHEN STR_TO_DATE(dt.tanggal_approval_aktivasi, '%Y-%m-%d') <= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                        THEN rp.id 
                    END) as top_up_existing_akun")
                )
                ->first();
            
            return response()->json([
                'total_leads' => $totalLeads ?? 0,
                'total_existing_akun' => $accountStats->existing_akun ?? 0,
                'total_new_akun' => $accountStats->new_akun ?? 0,
                'total_top_up_new_akun' => $topUpStats->top_up_new_akun ?? 0,
                'total_top_up_existing_akun' => $topUpStats->top_up_existing_akun ?? 0,
            ]);

        } catch (\Exception $e) {
            \Log::error("Error in getRegionalChartData: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
