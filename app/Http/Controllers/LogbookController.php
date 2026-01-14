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
        // =======================
    // BASE QUERY + JOIN LOGBOOK
    // =======================
    $query = LeadsMaster::query()
        ->with(['user', 'source', 'sector'])
        ->leftJoin('logbooks', 'logbooks.leads_master_id', '=', 'leads_masters.id')
        ->select(
            'leads_masters.*',
            'logbooks.komitmen',
            'logbooks.plan_min_topup'
        )
        ->orderBy('leads_masters.created_at', 'asc');

    // =======================
    // 🔐 FILTER ROLE
    // =======================
    if (!auth()->user()->hasRole('Admin')) {
        $query->where('leads_masters.user_id', auth()->id());
    }

    // =======================
    // 🔍 FILTER DATATABLE
    // =======================
    if ($request->regional) {
        $query->where('leads_masters.regional', $request->regional);
    }

    if ($request->canvasser) {
        $query->where('leads_masters.user_id', $request->canvasser);
    }

    if ($request->company) {
        $query->where('leads_masters.company_name', 'like', '%' . $request->company . '%');
    }

    if ($request->email) {
        $query->where('leads_masters.email', 'like', '%' . $request->email . '%');
    }

    if ($request->start_date && $request->end_date) {
        $query->whereBetween('leads_masters.created_at', [
            $request->start_date . ' 00:00:00',
            $request->end_date . ' 23:59:59',
        ]);
    }

    if ($request->source) {
        $query->whereHas('source', function ($q) use ($request) {
            $q->where('id', $request->source);
        });
    }

    // =======================
    // DATATABLE RESPONSE
    // =======================
    return datatables()->of($query)

        ->addColumn('user_name', function ($row) {
            return $row->user->name ?? '-';
        })

        ->addColumn('regional', function ($row) {
            return $row->regional ?? '-';
        })

        ->addColumn('company_name', function ($row) {
            return $row->company_name ?? '-';
        })

        ->addColumn('email', function ($row) {
            return $row->email ?? '-';
        })

        ->addColumn('mobile_phone', function ($row) {
            return $row->mobile_phone ?? '-';
        })

        ->addColumn('status', function ($row) {
            return $row->status == 1
                ? '<span class="badge badge-success">Deal</span>'
                : '<span class="badge badge-danger">Prospect</span>';
        })

        ->addColumn('komitmen', function ($row) {
            return $row->komitmen ?? '-';
        })

        ->addColumn('plan_min_topup', function ($row) {
            return $row->plan_min_topup
                ? number_format($row->plan_min_topup, 0, ',', '.')
                : '-';
        })

        ->editColumn('created_at', function ($row) {
            return $row->created_at->format('Y-m-d');
        })

        ->addColumn('aksi', function ($row) {
            return '
                <a href="' . route('leads-master.show', $row->id) . '" class="btn btn-sm btn-warning">
                    <i class="fas fa-search"></i> Lihat
                </a>
                <a href="' . route('leads-master.edit', $row->id) . '" class="btn btn-sm btn-primary">
                    <i class="fas fa-pencil-alt"></i> Edit
                </a>
            ';
        })

        ->rawColumns(['aksi', 'status'])
        ->make(true);
    }
}
