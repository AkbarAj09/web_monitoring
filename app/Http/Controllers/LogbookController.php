<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeadsMaster;
use App\Models\LeadsSource;
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
        // Base query + eager loading
        $query = LeadsMaster::with(['user', 'source', 'sector'])
            ->orderBy('created_at', 'asc');

        // 🔐 Filter berdasarkan role
        if (!auth()->user()->hasRole('Admin')) {
            $query->where('user_id', auth()->id());
        }

        // =======================
        // 🔍 FILTER DARI DATATABLE
        // =======================
        if ($request->regional) {
            $query->where('regional', $request->regional);
        }
        // Filter Canvasser
        if ($request->canvasser) {
            $query->where('user_id', $request->canvasser);
        }

        // Filter Nama Perusahaan
        if ($request->company) {
            $query->where('company_name', 'like', '%' . $request->company . '%');
        }

        // Filter Email
        if ($request->email) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // Filter Email
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }


        // Filter Lead Source (relasi)
        if ($request->source) {
            $query->whereHas('source', function ($q) use ($request) {
                $q->where('id', $request->source);
            });
        }

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
                return $row->status == 1 ? '<span class="badge badge-success">Deal</span>' : '<span class="badge badge-danger">Prospect</span>';
            })
            ->addColumn('data_type', function ($row) {
                return $row->data_type ?? '-';
            })->editColumn('created_at', function ($row) {
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
