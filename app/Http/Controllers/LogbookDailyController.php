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
use Carbon\Carbon;

class LogbookDailyController extends Controller
{
    public function index()
    {
        logUserLogin();
        return view('logbook.daily', [
            'canvassers' => User::orderBy('name')->where('role', 'cvsr')->orWhere('role', 'PH')->get(),
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
            ->join('logbook_daily', 'logbook_daily.leads_master_id', '=', 'leads_master.id')
            ->leftJoin('report_balance_top_up', function ($join) {
                $join->whereRaw('LOWER(report_balance_top_up.email_client) = LOWER(leads_master.email)');
            })
            ->leftJoin('manual_upload_topup', function ($join) {
                $join->whereRaw('LOWER(manual_upload_topup.email) = LOWER(leads_master.email)');
            })
            ->select([
                'leads_master.id',
                'users.name as user_name',
                'leads_master.regional',
                'leads_master.company_name',
                'leads_master.myads_account',
                'leads_master.mobile_phone',
                'leads_master.data_type',
                'logbook_daily.created_at',
                'logbook_daily.komitmen',
                'logbook_daily.plan_min_topup',
                'logbook_daily.status',
                'logbook_daily.realisasi_topup'
            ])
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
        // if ($request->regional) {
        //     $query->where('leads_master.regional', $request->regional);
        // }

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
            $query->whereBetween('logbook_daily.created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date   . ' 23:59:59',
            ]);
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
                ->filter(function ($query) use ($request) {
                    // Ambil value search dari input bawaan datatables
                    $search = $request->input('search.value');
                    
                    if (!empty($search)) {
                        $query->where(function($q) use ($search) {
                            $searchTerm = "%" . strtolower($search) . "%";
                            $q->whereRaw("LOWER(users.name) LIKE ?", [$searchTerm])
                            ->orWhereRaw("LOWER(leads_master.regional) LIKE ?", [$searchTerm])
                            ->orWhereRaw("LOWER(leads_master.company_name) LIKE ?", [$searchTerm])
                            ->orWhereRaw("LOWER(leads_master.myads_account) LIKE ?", [$searchTerm])
                            ->orWhereRaw("LOWER(leads_master.mobile_phone) LIKE ?", [$searchTerm]);
                        });
                    }
                })
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
                return \Carbon\Carbon::parse($row->created_at)->format('Y-m-d');
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
            ->addColumn('realisasi_topup', function ($row) {
                return $row->realisasi_topup
                    ? number_format($row->realisasi_topup, 0, ',', '.')
                    : '-';
            })
            //  ->addColumn('action', function ($row) {
            //     return '
            //     <button 
            //         class="btn btn-sm btn-primary btn-edit"
            //         data-id="'.$row->id.'"
            //         data-komitmen="'.$row->komitmen.'"
            //         data-plan="'.$row->plan_min_topup.'"
            //         data-status="'.$row->status.'"
            //     >
            //         Edit
            //     </button>
                
            //     <button 
            //         class="btn btn-sm btn-warning btn-day"
            //         data-id="'.$row->id.'"
            //     >
            //         Day
            //     </button>';
            // })
            ->rawColumns(['action'])
            ->make(true);
        }
        public function refreshLogbookDaily()
        {
            $month = now()->month;
            $year  = now()->year; // YYYY-MM-DD

            // Hitung realisasi topup per leads (AUTO prioritas, MANUAL fallback)
            $topups = DB::table('leads_master')
                ->leftJoin('report_balance_top_up', function ($join) use ($month, $year) {
                    $join->whereRaw('LOWER(report_balance_top_up.email_client) = LOWER(leads_master.email)')
                        ->whereMonth('report_balance_top_up.tgl_transaksi', $month)
                        ->whereYear('report_balance_top_up.tgl_transaksi', $year);
                })
                ->leftJoin('manual_upload_topup', function ($join) use ($month, $year) {
                    $join->whereRaw('LOWER(manual_upload_topup.email) = LOWER(leads_master.email)')
                        ->whereMonth('manual_upload_topup.tanggal', $month)
                        ->whereYear('manual_upload_topup.tanggal', $year);
                })
                ->select(
                    'leads_master.id as leads_master_id',

                    DB::raw('
                        CASE
                            WHEN COALESCE(SUM(report_balance_top_up.total_settlement_klien), 0) > 0
                            THEN SUM(report_balance_top_up.total_settlement_klien)
                            ELSE COALESCE(SUM(manual_upload_topup.total), 0)
                        END AS realisasi_topup
                    ')
                )
                ->groupBy('leads_master.id')
                ->having('realisasi_topup', '>', 0)
                ->get();


            foreach ($topups as $data) {
                DB::table('logbook_daily')
                    ->where('leads_master_id', $data->leads_master_id)
                    // ->whereDate('created_at', $today)
                    ->update([
                        'status'          => 'Topup',
                        'realisasi_topup' => $data->realisasi_topup,
                        'updated_at'      => now(),
                    ]);
                    print($data->leads_master_id. '    ');
            }
        }


}
