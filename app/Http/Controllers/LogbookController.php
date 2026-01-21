<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeadsMaster;
use App\Models\LeadsSource;
use App\Models\LogbookModel;
use App\Models\User;
use App\Models\Sector;
use Illuminate\Validation\Rule; 
use DataTables;
use Validator;
use Illuminate\Support\Facades\DB;

class LogbookController extends Controller
{
    /**
     * Show the leads master view
     */
    public function index()
    {
        logUserLogin();
        return view('logbook.index', [
            'canvassers' => User::orderBy('name')->get(),
            'sources'    => LeadsSource::orderBy('name')->get(),
            'regionals'  => DB::table('regional_provinces')
                                ->select('regional')
                                ->distinct()
                                ->orderBy('regional')
                                ->pluck('regional'),
        ]);
    }

    /**
     * Datatable server-side response
     */
    public function data(Request $request)
    {
        
        $search = $request->input('search.value');
        // =======================
        // BASE QUERY + JOIN LOGBOOK
        // =======================
        $query = LeadsMaster::query()
            ->leftJoin('users', 'users.id', '=', 'leads_master.user_id')
            ->join('logbook', 'logbook.leads_master_id', '=', 'leads_master.id')
            ->leftJoin('report_balance_top_up', function ($join) {
                    $join->whereRaw(
                        'LOWER(report_balance_top_up.email_client) = LOWER(leads_master.email)'
                    );
                })
            ->select([
                'leads_master.id',
                'users.name as user_name',
                'leads_master.regional',
                'leads_master.company_name',
                'leads_master.myads_account',
                'leads_master.mobile_phone',
                'leads_master.data_type',
                'leads_master.created_at',
                'logbook.komitmen',
                'logbook.plan_min_topup',
                'logbook.status',
                DB::raw('SUM(report_balance_top_up.total_settlement_klien) as total_settlement_klien'),
            ])
            ->groupBy(
                'leads_master.id',
                'users.name',
                'leads_master.regional',
                'leads_master.company_name',
                'leads_master.myads_account',
                'leads_master.mobile_phone',
                'leads_master.data_type',
                'leads_master.created_at',
                'logbook.komitmen',
                'logbook.plan_min_topup',
                'logbook.status'
            )
        ->orderBy('leads_master.created_at', 'desc');

        // =======================
        // 🔐 FILTER ROLE
        // =======================
        if (!auth()->user()->hasRole('Admin')) {
            $query->where('leads_master.user_id', auth()->id());
        }

        // =======================
        // 🔍 FILTER DATATABLE
        // =======================
        if ($request->regional) {
            $query->where('leads_master.regional', $request->regional);
        }

        if ($request->canvasser) {
            $query->where('leads_master.user_id', $request->canvasser);
        }

        // if ($request->company) {
        //     $query->where('leads_master.company_name', 'like', '%' . $request->company . '%');
        // }

        // if ($request->email) {
        //     $query->where('leads_master.email', 'like', '%' . $request->email . '%');
        // }

        if ($request->start_date && $request->end_date) {
            
            $date = Carbon\Carbon::createFromFormat('Y-m', $request->month);
            $query->whereBetween('leads_master.created_at', [
                $date->startOfMonth(),
                $date->endOfMonth(),
            ]);
        }
        if (!empty($search)) {
            $search = strtolower($search);

            $query->havingRaw("
                LOWER(users.name) LIKE ?
                OR LOWER(leads_master.regional) LIKE ?
                OR LOWER(leads_master.company_name) LIKE ?
                OR LOWER(leads_master.myads_account) LIKE ?
                OR LOWER(leads_master.mobile_phone) LIKE ?
            ", array_fill(0, 5, "%{$search}%"));
        }

        // if ($request->source) {
        //     $query->whereHas('source', function ($q) use ($request) {
        //         $q->where('id', $request->source);
        //     });
        // }

        // =======================
        // DATATABLE RESPONSE
        // =======================
        return datatables()->of($query)

            ->addColumn('user_name', function ($row) {
                return $row->user_name ?? '-';
            })

            ->addColumn('regional', function ($row) {
                return $row->regional ?? '-';
            })

            ->addColumn('company_name', function ($row) {
                return $row->company_name ?? '-';
            })

            ->addColumn('myads_account', function ($row) {
                return $row->myads_account ?? '-';
            })

            ->addColumn('mobile_phone', function ($row) {
                return $row->mobile_phone ?? '-';
            })

            ->addColumn('data_type', function ($row) {
                return $row->data_type ?? '-';
            })

            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d');
            })

            ->addColumn('komitmen', function ($row) {
                return $row->komitmen ?? '-';
            })

            ->addColumn('plan_min_topup', function ($row) {
                return $row->plan_min_topup
                    ? number_format($row->plan_min_topup, 0, ',', '.')
                    : '-';
            })
             ->addColumn('total_settlement_klien', function ($row) {
                return $row->total_settlement_klien
                    ? number_format($row->total_settlement_klien, 0, ',', '.')
                    : '-';
            })
             ->addColumn('status', function ($row) {
                return $row->status ?? '-';
            })
             ->addColumn('action', function ($row) {
                return '
                <button 
                    class="btn btn-sm btn-primary btn-edit"
                    data-id="'.$row->id.'"
                    data-komitmen="'.$row->komitmen.'"
                    data-plan="'.$row->plan_min_topup.'"
                    data-status="'.$row->status.'"
                >
                    Edit
                </button>
                
                <button 
                    class="btn btn-sm btn-warning btn-day"
                    data-id="'.$row->id.'"
                >
                    Day
                </button>';
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        public function update(Request $request)
        {
            
            $tes = DB::table('logbook')
                ->where('leads_master_id', $request->id)
                ->update([
                    'komitmen' => $request->komitmen,
                    'plan_min_topup' => $request->plan_min_topup,
                    'status' => $request->status,
                    'updated_at' => now(),
                ]);

            return response()->json(['success' => true]);
        }
    
    public function insert(Request $request)
    {
        // dd($request->all());
        $exists = DB::table('logbook')
            ->where('leads_master_id', $request->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Logbook sudah ada!');
        }
   
        DB::table('logbook')->insert([
            'leads_master_id' => $request->id,
            'komitmen'        => $request->komitmen,
            'plan_min_topup'  => $request->plan_min_topup,
            'status'          => $request->status,
            'bulan'           => now()->month,
            'tahun'           => now()->year,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    
        return redirect()->back()->with('success', 'Data berhasil disimpan!');
    }
    public function insertDaily(Request $request)
    {
        // Ambil data logbook berdasarkan leads_master_id
        $logbook = DB::table('logbook')
            ->where('leads_master_id', $request->id)
            ->first();

        if (!$logbook) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data logbook tidak ditemukan'
            ], 404);
        }

        // Cegah double input daily di hari yang sama
        $exists = DB::table('logbook_daily')
            ->where('leads_master_id', $request->id)
            ->whereDate('created_at', now())
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logbook daily hari ini sudah ada'
            ], 422);
        }

        // Insert ke logbook_daily
        DB::table('logbook_daily')->insert([
            'leads_master_id' => $request->id,
            'komitmen'        => $logbook->komitmen,
            'plan_min_topup'  => $logbook->plan_min_topup,
            'status'          => $logbook->status,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Logbook daily berhasil ditambahkan'
        ]);
    }


    public function refreshLogbookStatus()
    {
 
        $this->info('Starting to update leads status...');

        // Get leads where sum of report_balance_top_up.total_settlement_klien > 0
        $leads = DB::table('leads_master')
            ->join('logbook', 'logbook.leads_master_id', '=', 'leads_master.id')
            ->leftJoin('report_balance_top_up', function($join) {
                $join->whereRaw('LOWER(report_balance_top_up.email_client) = LOWER(leads_master.email)');
            })
            ->select(
                'leads_master.id',
                'logbook.id as logbook_id',
                DB::raw('SUM(report_balance_top_up.total_settlement_klien) as total_settlement')
            )
            ->groupBy('leads_master.id', 'logbook.id')
            ->havingRaw('total_settlement > 0')
            ->get();

        foreach ($leads as $lead) {
            // Update the logbook status
            DB::table('logbook')
                ->where('leads_master_id', $lead->logbook_id)
                ->update([
                    'status' => 'Topup', // or any status you want
                    'updated_at' => now(),
                ]);
        }

        $this->info('Leads status updated successfully!');
        
    }
}