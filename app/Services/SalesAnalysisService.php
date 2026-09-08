<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SalesAnalysisService
{
    public const CHANNELS = [
        'canvasser' => 'Canvasser', 'mitra_sbp' => 'Mitra SBP', 'agency' => 'Agency Indihome',
        'internal' => 'Internal', 'advertising' => 'Agency Advertising', 'b2b' => 'B2B',
        'powerhouse' => 'Powerhouse', 'am' => 'AM', 'self_service' => 'Self Service',
    ];

    public function period(string $month): array
    {
        $start = CarbonImmutable::parse($month.'-01')->startOfDay();
        $isCurrent = $month === now()->format('Y-m');
        $end = $isCurrent ? CarbonImmutable::today()->endOfDay() : $start->endOfMonth();
        $previousStart = $start->subMonthNoOverflow();
        $previousEnd = $isCurrent
            ? $previousStart->day(min($end->day, $previousStart->daysInMonth))->endOfDay()
            : $previousStart->endOfMonth();

        return compact('start', 'end', 'previousStart', 'previousEnd') + [
            'month' => $month,
            'elapsed_days' => $end->day,
            'remaining_days' => $start->daysInMonth - $end->day,
            'current_label' => $start->format('d M').' – '.$end->format('d M Y'),
            'previous_label' => $previousStart->format('d M').' – '.$previousEnd->format('d M Y'),
        ];
    }

    private function transactions(CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        return DB::table('report_balance_top_up as rp')
            ->where('rp.tgl_transaksi', '>=', $start->format('Y-m-d H:i:s'))
            ->where('rp.tgl_transaksi', '<', $end->addDay()->startOfDay()->format('Y-m-d H:i:s'))
            ->whereNotNull('rp.email_client')->whereNotNull('rp.total_settlement_klien')
            ->where('rp.payment_method_name', '!=', 'Voucher Bonus');
    }

    private function channelTransactions(CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        // Match Daily Topup Channel ownership precedence; deduplicate lookup rows before joining.
        $owners = DB::table('leads_master as lm')->join('users as u', 'u.id', '=', 'lm.user_id')
            ->where('u.role', '!=', 'MPCC')->whereNotNull('lm.email')
            ->selectRaw('LOWER(TRIM(lm.email)) as email_key, MIN(lm.user_id) as user_id')
            ->groupByRaw('LOWER(TRIM(lm.email))');
        $partners = DB::table('mitra_sbp')->selectRaw('LOWER(TRIM(email_myads)) as email_key, MIN(remark) as remark')
            ->groupByRaw('LOWER(TRIM(email_myads))');
        $b2b = DB::table('b2b_clients')->selectRaw('LOWER(TRIM(myads_account)) as email_key')
            ->distinct();

        return $this->transactions($start, $end)
            ->leftJoinSub($owners, 'owner', fn ($join) => $join->on(DB::raw('LOWER(TRIM(rp.email_client))'), '=', 'owner.email_key'))
            ->leftJoin('users as u', 'u.id', '=', 'owner.user_id')
            ->leftJoinSub($partners, 'partner', fn ($join) => $join->on(DB::raw('LOWER(TRIM(rp.email_client))'), '=', 'partner.email_key'))
            ->leftJoinSub($b2b, 'bc', fn ($join) => $join->on(DB::raw('LOWER(TRIM(rp.email_client))'), '=', 'bc.email_key'))
            ->selectRaw("rp.tgl_transaksi, LOWER(TRIM(rp.email_client)) as email_key,
                CAST(rp.total_settlement_klien AS DECIMAL(18,2)) as settlement,
                CASE
                    WHEN u.role = 'cvsr' AND u.name != 'self service' THEN 'canvasser'
                    WHEN bc.email_key IS NOT NULL THEN 'b2b'
                    WHEN u.role = 'PH' THEN 'powerhouse'
                    WHEN UPPER(u.role) = 'AM' THEN 'am'
                    WHEN partner.remark = 'Mitra SBP' THEN 'mitra_sbp'
                    WHEN partner.remark = 'Agency' THEN 'agency'
                    WHEN partner.remark = 'Internal' THEN 'internal'
                    WHEN partner.remark = 'B2B' THEN 'b2b'
                    WHEN partner.remark = 'Agency Advertising' THEN 'advertising'
                    ELSE 'self_service'
                END as channel");
    }

    public function trend(array $period, string $channel = 'all', bool $accounts = false): array
    {
        $series = [];
        foreach (['current' => ['start', 'end'], 'previous' => ['previousStart', 'previousEnd']] as $key => [$from, $to]) {
            $query = $channel === 'all'
                ? $this->transactions($period[$from], $period[$to])
                    ->selectRaw('rp.tgl_transaksi, LOWER(TRIM(rp.email_client)) as email_key, CAST(rp.total_settlement_klien AS DECIMAL(18,2)) as settlement')
                : $this->channelTransactions($period[$from], $period[$to]);
            $filtered = DB::query()->fromSub($query, 'tx')
                ->when($channel !== 'all', fn ($q) => $q->where('channel', $channel));
            if ($accounts) {
                // Count each normalized email on its first topup date in this period only.
                $firstTopups = $filtered->where('email_key', '!=', '')
                    ->selectRaw('email_key, MIN(DATE(tgl_transaksi)) as first_date')->groupBy('email_key');
                $daily = DB::query()->fromSub($firstTopups, 'first_topups')
                    ->selectRaw('first_date as date, COUNT(*) as total')->groupBy('first_date')->pluck('total', 'date');
            } else {
                $daily = $filtered->selectRaw('DATE(tgl_transaksi) as date, SUM(settlement) as total')
                    ->groupByRaw('DATE(tgl_transaksi)')->pluck('total', 'date');
            }
            $running = 0;
            $series[$key] = [];
            for ($day = 1; $day <= $period[$to]->day; $day++) {
                $date = $period[$from]->day($day)->format('Y-m-d');
                $running += (float) ($daily[$date] ?? 0);
                $series[$key][] = $accounts ? (int) $running : round($running, 2);
            }
        }

        return $series + ['labels' => range(1, max(count($series['current']), count($series['previous'])))];
    }

    public function retention(array $period, string $channel = 'all'): array
    {
        $january = $period['start']->startOfYear();
        $transactions = $channel === 'all'
            ? $this->transactions($january, $period['end'])
                ->selectRaw('rp.tgl_transaksi, LOWER(TRIM(rp.email_client)) as email_key')
            : $this->channelTransactions($january, $period['end']);
        $monthlyAccounts = DB::query()->fromSub($transactions, 'tx')
            ->where('email_key', '!=', '')
            ->when($channel !== 'all', fn ($query) => $query->where('channel', $channel))
            ->selectRaw('SUBSTR(tgl_transaksi, 1, 7) as topup_month, email_key')->distinct();

        // Follow each month's fixed account group in every subsequent month, including N+0.
        $counts = DB::query()->fromSub(clone $monthlyAccounts, 'cohort_accounts')
            ->joinSub(clone $monthlyAccounts, 'retained_accounts', function ($join) {
                $join->on('retained_accounts.email_key', '=', 'cohort_accounts.email_key')
                    ->on('retained_accounts.topup_month', '>=', 'cohort_accounts.topup_month');
            })
            ->selectRaw('cohort_accounts.topup_month as cohort_month, retained_accounts.topup_month as activity_month, COUNT(*) as accounts')
            ->groupBy('cohort_accounts.topup_month', 'retained_accounts.topup_month')->get()->groupBy('cohort_month');

        $offsets = range(0, $period['start']->month - 1);
        $rows = [];
        for ($date = $january; $date->lte($period['start']); $date = $date->addMonth()) {
            $month = $date->format('Y-m');
            $activity = ($counts[$month] ?? collect())->pluck('accounts', 'activity_month');
            $baseline = (int) ($activity[$month] ?? 0);
            $cells = [];
            foreach ($offsets as $offset) {
                $activityDate = $date->addMonths($offset);
                $activityMonth = $activityDate->format('Y-m');
                $isFuture = $activityDate->gt($period['start']);
                $retained = $isFuture ? null : (int) ($activity[$activityMonth] ?? 0);
                $cells[] = [
                    'month' => $activityMonth, 'count' => $retained,
                    'rate' => !$isFuture && $baseline > 0 ? round($retained / $baseline * 100, 2) : null,
                    'is_future' => $isFuture,
                    'is_partial' => !$isFuture && $activityMonth === now()->format('Y-m'),
                ];
            }
            $rows[] = [
                'month' => $month, 'label' => $date->locale('id')->translatedFormat('F Y'),
                'baseline' => $baseline, 'cells' => $cells,
            ];
        }

        return ['rows' => $rows, 'offsets' => $offsets,
            'range_label' => $january->locale('id')->translatedFormat('F Y').' – '.$period['end']->format('d M Y')];
    }

    public function channels(array $period): array
    {
        $aggregates = [];
        foreach (['current' => ['start', 'end'], 'previous' => ['previousStart', 'previousEnd']] as $key => [$from, $to]) {
            $aggregates[$key] = DB::query()->fromSub($this->channelTransactions($period[$from], $period[$to]), 'tx')
                ->selectRaw("channel, SUM(settlement) as total, COUNT(DISTINCT NULLIF(email_key, '')) as accounts")
                ->groupBy('channel')->get()->keyBy('channel');
        }
        $currentTotal = (float) $aggregates['current']->sum('total');
        $previousTotal = (float) $aggregates['previous']->sum('total');
        $rows = [];
        foreach (self::CHANNELS as $channel => $name) {
            $current = (float) ($aggregates['current'][$channel]->total ?? 0);
            $previous = (float) ($aggregates['previous'][$channel]->total ?? 0);
            $rows[] = [
                'channel' => $channel, 'name' => $name, 'current' => $current, 'previous' => $previous,
                'share' => $currentTotal > 0 ? $current / $currentTotal * 100 : 0,
                'growth' => $previous > 0 ? ($current - $previous) / $previous * 100 : null,
                'accounts' => (int) ($aggregates['current'][$channel]->accounts ?? 0),
            ];
        }
        usort($rows, fn ($a, $b) => $b['current'] <=> $a['current']);

        return ['rows' => $rows, 'summary' => [
            'total' => $currentTotal, 'previous' => $previousTotal,
            'growth' => $previousTotal > 0 ? ($currentTotal - $previousTotal) / $previousTotal * 100 : null,
            'accounts' => (int) $aggregates['current']->sum('accounts'),
        ]];
    }
}
