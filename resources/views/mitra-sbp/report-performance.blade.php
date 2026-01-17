@extends('master')

@section('title', 'Report Target vs Topup Region')

@section('content')
<table>
<tbody>

@foreach($grouped as $area => $rows)

    {{-- HEADER AREA --}}
    <tr class="table-success font-weight-bold">
        <td>{{ $area }}</td>
        <td>{{ number_format($rows->sum('target')) }}</td>
        <td>{{ number_format($rows->sum('mitra_sbp')) }}</td>
        <td>
            {{ 
                $rows->sum('target') > 0 
                ? round(($rows->sum('mitra_sbp') / $rows->sum('target')) * 100, 2) 
                : 0 
            }}%
        </td>
    </tr>

    {{-- DETAIL REGION --}}
    @foreach($rows as $row)
    <tr>
        <td>{{ $row->region_name }}</td>
        <td>{{ number_format($row->target_amount) }}</td>
        <td>{{ number_format($row->mitra_sbp) }}</td>
        <td>{{ $row->ach_to_target }}%</td>
    </tr>
    @endforeach

@endforeach

{{-- TOTAL KESELURUHAN --}}
<tr class="table-danger font-weight-bold">
    <td>Total</td>
    <td>{{ number_format($data->sum('target')) }}</td>
    <td>{{ number_format($data->sum('mitra_sbp')) }}</td>
    <td>
        {{ 
            $data->sum('target') > 0 
            ? round(($data->sum('mitra_sbp') / $data->sum('target')) * 100, 2) 
            : 0 
        }}%
    </td>
</tr>

</tbody>
</table>



{{-- ================= JS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

@endsection