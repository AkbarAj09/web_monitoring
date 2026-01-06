@extends('master')

@section('title', 'Report Target vs Topup Region')

@section('content')
<div class="container-fluid">

    {{-- ================= HORIZONTAL BAR CHART ================= --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Target vs Topup per Region (Jan 2026)</h5>
        </div>
        <div class="card-body">
            <canvas id="regionBarChart" height="100"></canvas>
        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card">
        <div class="card-body">

            {{-- AREA CAPTURE (TABLE SAJA) --}}
            <div id="captureTable">

                <h5 class="text-center mb-3">
                    Target vs Topup per Region – Jan 2026
                </h5>

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
                                    <td class="text-end">{{ number_format($row['target'], 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($row['topup'], 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge
                                            {{ $row['percentage'] >= 90 ? 'bg-success' : ($row['percentage'] >= 70 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $row['percentage'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-center">TOTAL</td>
                                <td class="text-end">{{ number_format(collect($data)->sum('target'), 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format(collect($data)->sum('topup'), 0, ',', '.') }}</td>
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
            {{-- END CAPTURE --}}

            {{-- BUTTON BESAR DI BAWAH --}}
            <div class="text-center mt-4">
                <button id="btnSaveImage" class="btn btn-primary btn-lg px-5 py-3 shadow">
                    📸 Save Table as Image
                </button>
            </div>

        </div>
    </div>

</div>

{{-- ================= JS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ================= DATATABLE ================= */
    $('#regionTable').DataTable({
        paging: false,
        searching: false,
        info: false,
        order: [[0, 'asc']],
        columnDefs: [
            { targets: [0], orderable: true },
            { targets: [3,4], className: 'text-end' },
            { targets: [5], className: 'text-center' }
        ]
    });

    /* ================= HORIZONTAL BAR CHART ================= */
    const rawData = @json($data);

    const labels = rawData.map(r => r.region);
    const targetData = rawData.map(r => Number(r.target) || 0);
    const topupData  = rawData.map(r => Number(r.topup) || 0);

    new Chart(document.getElementById('regionBarChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Target', data: targetData },
                { label: 'Topup', data: topupData }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: ctx =>
                            ctx.dataset.label + ': ' +
                            new Intl.NumberFormat('id-ID').format(ctx.raw)
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: value =>
                            new Intl.NumberFormat('id-ID').format(value)
                    }
                }
            }
        }
    });

    /* ================= SAVE TABLE AS IMAGE ================= */
    document.getElementById('btnSaveImage').addEventListener('click', function () {
        html2canvas(document.getElementById('captureTable'), {
            scale: 2,
            useCORS: true
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'target-vs-topup-region.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    });

});
</script>

@endsection
