@extends('master')

@section('title', 'Report Performance')

@section('content')

<table>
<tbody>

@foreach($grouped as $area => $rows)

@php
    $areaTarget = $rows->sum('target_amount');
    $areaMitra  = $rows->sum('mitra_sbp');
@endphp

{{-- HEADER AREA --}}
<tr class="table-success font-weight-bold">
    <td>{{ $area }}</td>
    <td>{{ number_format($areaTarget) }}</td>
    <td>{{ number_format($areaMitra) }}</td>
    <td>
        {{ $areaTarget > 0 ? round(($areaMitra / $areaTarget) * 100, 2) : 0 }}%
    </td>
</tr>

{{-- DETAIL REGION --}}
@foreach($rows as $row)
<tr>
    <td>{{ $row->region_name }}</td>
    <td>{{ number_format($row->target_amount) }}</td>
    <td>{{ number_format($row->mitra_sbp) }}</td>
    <td>
        {{ $row->target_amount > 0
            ? round(($row->mitra_sbp / $row->target_amount) * 100, 2)
            : 0
        }}%
    </td>
</tr>
@endforeach

@endforeach

{{-- TOTAL --}}
@php
    $totalTarget = $data->sum('target_amount');
    $totalMitra  = $data->sum('mitra_sbp');
@endphp

<tr class="table-danger font-weight-bold">
    <td>Total</td>
    <td>{{ number_format($totalTarget) }}</td>
    <td>{{ number_format($totalMitra) }}</td>
    <td>
        {{ $totalTarget > 0 ? round(($totalMitra / $totalTarget) * 100, 2) : 0 }}%
    </td>
</tr>

</tbody>
</table>



@endsection
@section('js')
{{-- ================= JS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

@endsection