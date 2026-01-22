@extends('master')

@section('title', 'Report Performance')

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="card" id="mitraSBPTableCard">
            <div class="card-header bg-gradient-success text-white">
                <h4 class="mb-0"><i class="fas fa-table"></i> Report Mitra SBP</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle">
                        <thead>
                            <tr class="text-center" style="background-color:#e3f2fd;">
                                <th>Area / Region</th>
                                <th>Target</th>
                                <th>Realisasi</th>
                                <th>Achievement (%)</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach($grouped_mitra_sbp as $area => $rows)

                            @php
                                $areaTarget = $rows->sum('target_amount');
                                $areaMitra  = $rows->sum('mitra_sbp');
                                $areaPct    = $areaTarget > 0 ? round(($areaMitra / $areaTarget) * 100, 2) : 0;
                            @endphp

                            {{-- HEADER AREA --}}
                            <tr style="background: linear-gradient(90deg,#d1ecf1,#f8f9fa); font-weight:bold;">
                                <td class="text-start">
                                    <i class="fas fa-layer-group text-info me-1"></i>
                                    {{ $area }}
                                </td>
                                <td>{{ number_format($areaTarget) }}</td>
                                <td>{{ number_format($areaMitra) }}</td>
                                <td>
                                    <span class="badge {{ $areaPct >= 100 ? 'bg-success' : ($areaPct >= 75 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ $areaPct }}%
                                    </span>
                                </td>
                            </tr>

                            {{-- DETAIL REGION --}}
                            @foreach($rows as $row)
                            @php
                                $pct = $row->target_amount > 0
                                    ? round(($row->mitra_sbp / $row->target_amount) * 100, 2)
                                    : 0;
                            @endphp
                            <tr>
                                <td class="text-start ps-4">
                                    <i class="fas fa-map-marker-alt text-secondary me-1"></i>
                                    {{ $row->region_name }}
                                </td>
                                <td>{{ number_format($row->target_amount) }}</td>
                                <td>{{ number_format($row->mitra_sbp) }}</td>
                                <td>
                                    <span class="text-{{ $pct >= 100 ? 'success' : ($pct >= 75 ? 'warning' : 'danger') }} fw-bold">
                                        {{ $pct }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach

                        @endforeach
                        </tbody>

                        {{-- TOTAL --}}
                        @php
                            $totalTarget = $data_mitra_sbp->sum('target_amount');
                            $totalMitra  = $data_mitra_sbp->sum('mitra_sbp');
                            $totalPct    = $totalTarget > 0 ? round(($totalMitra / $totalTarget) * 100, 2) : 0;
                        @endphp

                        <tfoot>
                            <tr style="background-color:#fff3cd; font-weight:bold; border-top:2px solid #ffc107;">
                                <td class="text-start">
                                    <i class="fas fa-calculator me-1"></i> TOTAL
                                </td>
                                <td>{{ number_format($totalTarget) }}</td>
                                <td>{{ number_format($totalMitra) }}</td>
                                <td>
                                    <span class="badge bg-{{ $totalPct >= 100 ? 'success' : ($totalPct >= 75 ? 'warning' : 'danger') }}">
                                        {{ $totalPct }}%
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>


<div class="row mb-4">
    <div class="col-12">
        <div class="card" id="agencyTableCard">
            <div class="card-header bg-gradient-info text-white">
                <h4 class="mb-0"><i class="fas fa-table"></i> Report Agency Indihome</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle">
                        <thead>
                            <tr class="text-center" style="background-color:#e3f2fd;">
                                <th>Area / Region</th>
                                <th>Target</th>
                                <th>Realisasi</th>
                                <th>Achievement (%)</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach($grouped_agency as $area => $rows)

                            @php
                                $areaTarget = $rows->sum('target_amount');
                                $areaMitra  = $rows->sum('agency');
                                $areaPct    = $areaTarget > 0 ? round(($areaMitra / $areaTarget) * 100, 2) : 0;
                            @endphp

                            {{-- HEADER AREA --}}
                            <tr style="background: linear-gradient(90deg,#d1ecf1,#f8f9fa); font-weight:bold;">
                                <td class="text-start">
                                    <i class="fas fa-layer-group text-info me-1"></i>
                                    {{ $area }}
                                </td>
                                <td>{{ number_format($areaTarget) }}</td>
                                <td>{{ number_format($areaMitra) }}</td>
                                <td>
                                    <span class="badge {{ $areaPct >= 100 ? 'bg-success' : ($areaPct >= 75 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ $areaPct }}%
                                    </span>
                                </td>
                            </tr>

                            {{-- DETAIL REGION --}}
                            @foreach($rows as $row)
                            @php
                                $pct = $row->target_amount > 0
                                    ? round(($row->agency / $row->target_amount) * 100, 2)
                                    : 0;
                            @endphp
                            <tr>
                                <td class="text-start ps-4">
                                    <i class="fas fa-map-marker-alt text-secondary me-1"></i>
                                    {{ $row->region_name }}
                                </td>
                                <td>{{ number_format($row->target_amount) }}</td>
                                <td>{{ number_format($row->agency) }}</td>
                                <td>
                                    <span class="text-{{ $pct >= 100 ? 'success' : ($pct >= 75 ? 'warning' : 'danger') }} fw-bold">
                                        {{ $pct }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach

                        @endforeach
                        </tbody>

                        {{-- TOTAL --}}
                        @php
                            $totalTarget = $data_agency->sum('target_amount');
                            $totalMitra  = $data_agency->sum('agency');
                            $totalPct    = $totalTarget > 0 ? round(($totalMitra / $totalTarget) * 100, 2) : 0;
                        @endphp

                        <tfoot>
                            <tr style="background-color:#fff3cd; font-weight:bold; border-top:2px solid #ffc107;">
                                <td class="text-start">
                                    <i class="fas fa-calculator me-1"></i> TOTAL
                                </td>
                                <td>{{ number_format($totalTarget) }}</td>
                                <td>{{ number_format($totalMitra) }}</td>
                                <td>
                                    <span class="badge bg-{{ $totalPct >= 100 ? 'success' : ($totalPct >= 75 ? 'warning' : 'danger') }}">
                                        {{ $totalPct }}%
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>



<div class="row mb-4">
    <div class="col-12">
        <div class="card" id="internalTableCard">
            <div class="card-header bg-gradient-danger text-white">
                <h4 class="mb-0"><i class="fas fa-table"></i> Report Internal Indihome</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle">
                        <thead>
                            <tr class="text-center" style="background-color:#e3f2fd;">
                                <th>Area / Region</th>
                                <th>Target</th>
                                <th>Realisasi</th>
                                <th>Achievement (%)</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach($grouped_internal as $area => $rows)

                            @php
                                $areaTarget = $rows->sum('target_amount');
                                $areaMitra  = $rows->sum('internal');
                                $areaPct    = $areaTarget > 0 ? round(($areaMitra / $areaTarget) * 100, 2) : 0;
                            @endphp

                            {{-- HEADER AREA --}}
                            <tr style="background: linear-gradient(90deg,#d1ecf1,#f8f9fa); font-weight:bold;">
                                <td class="text-start">
                                    <i class="fas fa-layer-group text-info me-1"></i>
                                    {{ $area }}
                                </td>
                                <td>{{ number_format($areaTarget) }}</td>
                                <td>{{ number_format($areaMitra) }}</td>
                                <td>
                                    <span class="badge {{ $areaPct >= 100 ? 'bg-success' : ($areaPct >= 75 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ $areaPct }}%
                                    </span>
                                </td>
                            </tr>

                            {{-- DETAIL REGION --}}
                            @foreach($rows as $row)
                            @php
                                $pct = $row->target_amount > 0
                                    ? round(($row->internal / $row->target_amount) * 100, 2)
                                    : 0;
                            @endphp
                            <tr>
                                <td class="text-start ps-4">
                                    <i class="fas fa-map-marker-alt text-secondary me-1"></i>
                                    {{ $row->region_name }}
                                </td>
                                <td>{{ number_format($row->target_amount) }}</td>
                                <td>{{ number_format($row->internal) }}</td>
                                <td>
                                    <span class="text-{{ $pct >= 100 ? 'success' : ($pct >= 75 ? 'warning' : 'danger') }} fw-bold">
                                        {{ $pct }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach

                        @endforeach
                        </tbody>

                        {{-- TOTAL --}}
                        @php
                            $totalTarget = $data_internal->sum('target_amount');
                            $totalMitra  = $data_internal->sum('internal');
                            $totalPct    = $totalTarget > 0 ? round(($totalMitra / $totalTarget) * 100, 2) : 0;
                        @endphp

                        <tfoot>
                            <tr style="background-color:#fff3cd; font-weight:bold; border-top:2px solid #ffc107;">
                                <td class="text-start">
                                    <i class="fas fa-calculator me-1"></i> TOTAL
                                </td>
                                <td>{{ number_format($totalTarget) }}</td>
                                <td>{{ number_format($totalMitra) }}</td>
                                <td>
                                    <span class="badge bg-{{ $totalPct >= 100 ? 'success' : ($totalPct >= 75 ? 'warning' : 'danger') }}">
                                        {{ $totalPct }}%
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>



@endsection
@section('js')
{{-- ================= JS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

@endsection