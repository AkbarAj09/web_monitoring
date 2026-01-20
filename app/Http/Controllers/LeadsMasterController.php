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

class LeadsMasterController extends Controller
{
    /**
     * Show the leads master view
     */
    public function index()
    {
        logUserLogin();
        return view('leads-master.index', [
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
        // if ($request->canvasser) {
        //     $query->where('user_id', $request->canvasser);
        // }

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
        if ($search) {
            $query->where(function ($q) use ($search) {

                // 🔎 search dari relasi user
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%$search%");
                })

                // 🔎 search kolom di tabel leads_master
                ->orWhere('regional', 'like', "%$search%")
                ->orWhere('company_name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('mobile_phone', 'like', "%$search%");
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

    public function export(Request $request)
    {
        $query = LeadsMaster::with(['user', 'source', 'sector'])
            ->orderBy('created_at', 'asc');

        // 🔐 ROLE
        if (!auth()->user()->hasRole('Admin')) {
            $query->where('user_id', auth()->id());
        }

        /* ===== FILTER (SAMA DENGAN DATATABLE) ===== */
        // if ($request->canvasser) {
        //     $query->where('user_id', $request->canvasser);
        // }

        if ($request->company) {
            $query->where('company_name', 'like', '%' . $request->company . '%');
        }

        if ($request->email) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $filename = 'leads_master_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = [
            'Status',
            'Canvasser',
            'Regional',
            'Nama Perusahaan',
            'Email',
            'No HP',
            'Tipe Data',
            'Tanggal',
        ];

        $callback = function () use ($query, $columns) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM (biar Excel tidak rusak)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, $columns);

            foreach ($query->cursor() as $row) {
                fputcsv($file, [
                    $row->status == 1 ? 'Deal' : 'No Deal',
                    $row->user->name ?? '-',
                    $row->regional ?? '-',
                    $row->company_name ?? '-',
                    $row->email ?? '-',
                    $row->mobile_phone ?? '-',
                    $row->data_type ?? '-',
                    $row->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    public function create()
    {
        logUserLogin();
        $leadSources = LeadsSource::all();
        $sectors = Sector::all();

        return view('leads-master.create', compact('leadSources', 'sectors'));
    }
    public function createExisting()
    {
        logUserLogin();
        $leadSources = LeadsSource::all();
        $sectors = Sector::all();

        return view('leads-master.create-existing', compact('leadSources', 'sectors'));
    }
    public function store(Request $request)
    {
        // Custom validation rules
        $rules = [
            'user_id' => 'required|exists:users,id',
            'source_id' => 'required|exists:leads_source,id',
            'sector_id' => 'required|exists:sectors,id',
            // 'kode_voucher' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'mobile_phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^62\d{9,12}$/',
                'unique:leads_master,mobile_phone',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:leads_master,email',
            ],
            // 'status' => 'required|in:Ok,No',
            'nama' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'remarks' => 'nullable|string|max:1000',
            'myads_account' => 'nullable|string|max:255'
        ];

        $messages = [
            'mobile_phone.regex' => 'Nomor HP harus diawali dengan kode negara 62 dan hanya angka (9-12 digit).',
            'mobile_phone.unique' => 'Nomor HP sudah terdaftar.',
            'email.unique' => 'Email sudah terdaftar.',
        ];

        $validated = $request->validate($rules, $messages);

        // $statusValue = $validated['status'] === 'Ok' ? 1 : 0;
        $statusValue = 1; // Default ke 1 (Yes) karena field status di form disembunyikan
        $leads = LeadsMaster::create([
            'user_id' => $validated['user_id'],
            'source_id' => $validated['source_id'],
            'sector_id' => $validated['sector_id'] ?? null,
            // 'kode_voucher' => $validated['kode_voucher'],
            'company_name' => $validated['company_name'] ?? null,
            'mobile_phone' => $validated['mobile_phone'],
            'email' => $validated['email'] ?? null,
            'status' => $statusValue,  // simpan 1 untuk Ok, 0 untuk No
            'nama' => $validated['nama'],
            'address' => $validated['address'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'myads_account' => $validated['myads_account'] ?? null,
            'data_type' => 'Leads',
        ]);
        DB::table('logbook')->insert([
            'leads_master_id' => $leads->id,
            'komitmen'        => 'New Leads',
            'plan_min_topup'  => 100000,
            'status'          => 'Prospect',
            'bulan'           => now()->month,
            'tahun'           => now()->year,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()->route('leads-master.index')->with('success', 'Leads baru berhasil disimpan.');
    }

    public function storeExisting(Request $request)
    {
        // dd('test');
        // Custom validation rules
        $rules = [
            'user_id' => 'required|exists:users,id',
            // 'source_id' => 'required|exists:leads_source,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'company_name' => 'nullable|string|max:255',
            'mobile_phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^62\d{9,12}$/',
                'unique:leads_master,mobile_phone',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:leads_master,email',
            ],
            // 'status' => 'required|in:Ok,No',
            'nama' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'remarks' => 'nullable|string|max:1000',
            'myads_account' => 'required|string|max:255'
        ];

        $messages = [
            'mobile_phone.regex' => 'Nomor HP harus diawali dengan kode negara 62 dan hanya angka (9-12 digit).',
            'mobile_phone.unique' => 'Nomor HP sudah terdaftar.',
            'email.unique' => 'Email sudah terdaftar.',
        ];

        $validated = $request->validate($rules, $messages);

        // $statusValue = $validated['status'] === 'Ok' ? 1 : 0;
        $statusValue = 1; // Default ke 1 (Yes) karena field status di form disembunyikan
        LeadsMaster::create([
            'user_id' => $validated['user_id'],
            'source_id' => null,
            'sector_id' => $validated['sector_id'] ?? null,
            // 'kode_voucher' => $validated['kode_voucher'],
            'company_name' => $validated['company_name'] ?? null,
            'mobile_phone' => $validated['mobile_phone'],
            'email' => $validated['email'] ?? null,
            'status' => $statusValue,  // simpan 1 untuk Ok, 0 untuk No
            'nama' => $validated['nama'],
            'address' => $validated['address'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'myads_account' => $validated['myads_account'],
            'data_type' => 'Eksisting Akun',
        ]);


        return redirect()->route('leads-master.index')->with('success', 'Leads baru berhasil disimpan.');
    }

    public function show($id)
    {
        logUserLogin();
        // Load lead beserta relasi
        $lead = LeadsMaster::with(['user', 'source', 'sector'])->findOrFail($id);

        return view('leads-master.show', compact('lead'));
    }

    public function edit(LeadsMaster $lead)
    {
        logUserLogin();
        
        $leadSources = LeadsSource::all();
        $sectors = Sector::all();
        return view('leads-master.edit', compact('lead', 'leadSources', 'sectors'));
    }

    public function update(Request $request, LeadsMaster $lead)
    {
        $lead = LeadsMaster::findOrFail($lead->id);
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'source_id' => 'nullable|exists:leads_source,id',
            'sector_id' => 'required|exists:sectors,id',
            // 'kode_voucher' => 'string|max:255',
            'company_name' => 'nullable|string|max:255',
            'mobile_phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^62\d{9,12}$/',
                Rule::unique('leads_master', 'mobile_phone')->ignore($lead->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('leads_master', 'email')->ignore($lead->id),
            ],
            // 'status' => 'required|in:Ok,No',
            'nama' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'remarks' => 'nullable|string|max:1000',
            'myads_account' => 'nullable|string|max:255'
        ]);

        $lead->update([
            // 'kode_voucher' => $request->kode_voucher,
            'company_name' => $request->company_name,
            'mobile_phone' => $request->mobile_phone,
            'email' => $request->email,
            'source_id' => $request->source_id,
            'nama' => $request->nama,
            'sector_id' => $request->sector_id,
            // 'status' => $request->status == 'Ok' ? 1 : 0,
            'remarks' => $request->remarks,
            'myads_account' => $request->myads_account,
        ]);

        return redirect()->route('leads-master.index')->with('success', 'Lead berhasil diupdate');
    }

}
