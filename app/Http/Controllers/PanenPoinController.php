<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPanenPoin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PanenPoinController extends Controller
{
    // Tampilkan halaman input data
    public function index()
    {
        return view('panenpoin.inputdatapoin');
    }
    
    // Simpan data panen poin
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'akun_myads_pelanggan' => 'required|max:255',
            'nomor_hp_pelanggan' => 'required|string|max:20',
        ]);
        
        try {
            UserPanenPoin::create([
                'user_id' => Auth::id(),
                'nama_pelanggan' => $request->nama_pelanggan,
                'akun_myads_pelanggan' => strtolower($request->akun_myads_pelanggan),
                'nomor_hp_pelanggan' => $request->nomor_hp_pelanggan,
            ]);
            
            return redirect()->route('panenpoin.index')
                ->with('success', 'Data pelanggan berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    // Tampilkan halaman report
    public function report()
    {
        return view('panenpoin.reportpoin');
    }
    
    // Get data untuk DataTable
    public function getReportData(Request $request)
    {
        try {
            // Ambil bulan dan tahun dari request, default bulan dan tahun sekarang
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);
            
            $result = $this->calculatePanenPoinData($month, $year);
            
            return datatables()->of(collect($result))
                ->toJson();
                
        } catch (\Exception $e) {
            \Log::error("Error in getReportData: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // Hitung data panen poin
    private function calculatePanenPoinData($month, $year)
    {
        try {
            // Tentukan range tanggal
            $startDate = Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
            
            // Ambil semua canvasser (user dengan role 'cvsr')
            $canvassers = User::where('role', 'cvsr')->get();
            
            $result = [];
            
            foreach ($canvassers as $canvasser) {
                // Ambil email dari user_panen_poin yang diinput oleh canvasser ini
                $panenPoinData = UserPanenPoin::where('user_id', $canvasser->id)
                    ->select('akun_myads_pelanggan', 'nomor_hp_pelanggan')
                    ->get();
                
                $clientEmails = [];
                
                // Jika ada data di user_panen_poin, gunakan itu
                if ($panenPoinData->isNotEmpty()) {
                    foreach ($panenPoinData as $data) {
                        $clientEmails[] = [
                            'email' => strtolower(trim($data->akun_myads_pelanggan)),
                            'nomor_hp' => $data->nomor_hp_pelanggan,
                            'source' => 'user_panen_poin'
                        ];
                    }
                } else {
                    // Jika tidak ada di user_panen_poin, ambil dari leads_master
                    $leadsData = DB::table('leads_master')
                        ->where('user_id', $canvasser->id)
                        ->select('email', 'mobile_phone')
                        ->get();
                    
                    foreach ($leadsData as $lead) {
                        $clientEmails[] = [
                            'email' => strtolower(trim($lead->email)),
                            'nomor_hp' => $lead->mobile_phone ?? '-',
                            'source' => 'leads_master'
                        ];
                    }
                }
                
                // Loop setiap client dan hitung settlement-nya
                foreach ($clientEmails as $client) {
                    // Hitung total settlement dari report_balance_top_up untuk client ini
                    $totalSettlement = DB::table('report_balance_top_up')
                        ->whereBetween('tgl_transaksi', [$startDate, $endDate])
                        ->whereRaw('LOWER(TRIM(email_client)) = ?', [$client['email']])
                        ->whereNotNull('total_settlement_klien')
                        ->sum(DB::raw('CAST(total_settlement_klien AS DECIMAL(15,2))'));
                    
                    // Hitung poin: setiap 250.000 = 1 poin
                    $poin = floor($totalSettlement / 250000);
                    
                    $result[] = [
                        'nama_canvasser' => $canvasser->name,
                        'email_client' => $client['email'],
                        'nomor_hp_client' => $client['nomor_hp'],
                        'total_settlement' => number_format($totalSettlement, 0, ',', '.'),
                        'total_settlement_raw' => $totalSettlement,
                        'poin' => $poin,
                        'bulan' => Carbon::create($year, $month, 1)->locale('id')->translatedFormat('F Y')
                    ];
                }
            }
            
            return $result;
            
        } catch (\Exception $e) {
            \Log::error("Error in calculatePanenPoinData: " . $e->getMessage());
            return [];
        }
    }
    
    // Export ke Excel
    public function export(Request $request)
    {
        try {
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);
            
            $data = $this->calculatePanenPoinData($month, $year);
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Header
            $monthYear = Carbon::create($year, $month, 1)->locale('id')->translatedFormat('F Y');
            $sheet->setCellValue('A1', 'LAPORAN PANEN POIN - ' . strtoupper($monthYear));
            $sheet->mergeCells('A1:F1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Column headers
            $sheet->setCellValue('A3', 'No');
            $sheet->setCellValue('B3', 'Nama Canvasser');
            $sheet->setCellValue('C3', 'Email Client');
            $sheet->setCellValue('D3', 'Nomor HP Client');
            $sheet->setCellValue('E3', 'Total Settlement');
            $sheet->setCellValue('F3', 'Poin');
            
            $sheet->getStyle('A3:F3')->getFont()->setBold(true);
            $sheet->getStyle('A3:F3')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD9D9D9');
            
            // Data
            $row = 4;
            $no = 1;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $item['nama_canvasser']);
                $sheet->setCellValue('C' . $row, $item['email_client']);
                $sheet->setCellValue('D' . $row, $item['nomor_hp_client']);
                $sheet->setCellValue('E' . $row, $item['total_settlement']);
                $sheet->setCellValue('F' . $row, $item['poin']);
                $row++;
            }
            
            // Auto width
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Download
            $fileName = 'Laporan_Panen_Poin_' . $monthYear . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            \Log::error("Error in export: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }
}
