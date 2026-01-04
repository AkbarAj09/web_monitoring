<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function topupCanvasser(Request $request)
    {
        $start = $request->start ?? now()->subMonth()->startOfMonth();
        $end   = $request->end ?? now()->endOfMonth();

        $rows = DB::table('topup')
            ->join('leads_master', 'leads_master.email', '=', 'topup.email_client')
            ->join('users', 'users.id', '=', 'leads_master.user_id') // 🔥 JOIN USER
            ->selectRaw("
                DATE(topup.tgl_transaksi) as tanggal,
                users.name as canvasser,
                SUM(topup.total_settlement_klien) as total_amount,
                COUNT(DISTINCT topup.email_client) as total_email
            ")
            ->whereBetween('topup.tgl_transaksi', [$start, $end])
            ->where('users.role', 'Canvasser')
            ->groupBy('tanggal', 'users.name')
            ->orderBy('tanggal', 'desc')
            ->get();

        // === PIVOT ===
        $dates = $rows->groupBy('tanggal');
        $canvassers = $rows->pluck('canvasser')->unique()->values();

        $data = [];

        foreach ($dates as $tanggal => $items) {
            $row = [
                'tanggal' => \Carbon::parse($tanggal)->format('d M Y'),
                'total_amount' => 0,
                'total_email' => 0,
            ];

            foreach ($canvassers as $c) {
                $match = $items->firstWhere('canvasser', $c);

                $amount = $match->total_amount ?? 0;
                $email  = $match->total_email ?? 0;

                $row[$c.'_amount'] = $amount ?: '-';
                $row[$c.'_email']  = $email ?: '-';

                $row['total_amount'] += $amount;
                $row['total_email']  += $email;
            }

            $data[] = $row;
        }

        return response()->json([
            'canvassers' => $canvassers,
            'data' => $data
        ]);
    }

}
