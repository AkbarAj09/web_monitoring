@extends('master')

@section('title', 'Report Target vs Topup Region')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Target vs Topup per Region (Jan 2026)</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="regionTable" class="table table-bordered table-striped table-hover w-100">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Region</th>
                            <th>Nama PIC</th>
                            <th>Target</th>
                            <th>Topup</th>
                            <th>Achievement (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row['region'] }}</td>
                                <td>{{ $row['pic'] }}</td>
                                <td class="text-end">
                                    {{ number_format($row['target'], 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format($row['topup'], 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge 
                                        {{ $row['percentage'] >= 90 ? 'bg-success' : ($row['percentage'] >= 70 && $row['percentage'] < 90 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $row['percentage'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2" class="text-center">TOTAL</td>
                            <td class="text-end">
                                {{ number_format(collect($data)->sum('target'), 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{ number_format(collect($data)->sum('topup'), 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                {{
                                    collect($data)->sum('target') > 0
                                        ? round((collect($data)->sum('topup') / collect($data)->sum('target')) * 100, 2)
                                        : 0
                                }}%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#regionTable').DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[4, 'desc']],
        columnDefs: [
            { targets: [0], orderable: false },
            { targets: [2, 3], className: 'text-end' },
            { targets: [4], className: 'text-center' }
        ]
    });
});
</script>
@endpush
