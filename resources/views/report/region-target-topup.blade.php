@extends('master')

@section('title', 'Report Target vs Topup Region')

@section('content')
<div class="container-fluid">

    {{-- ================= FILTER BULAN ================= --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Pilih Bulan</label>
                    <input type="month"
                           name="month"
                           class="form-control"
                           value="{{ $month }}"
                           onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    {{-- ================= CHART ================= --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                Target vs Topup per Region
                ({{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') }})
            </h5>
        </div>
        <div class="card-body" style="height: 400px">
            <canvas id="regionBarChart" height="100"></canvas>
        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card">
        <div class="card-body">

            <div id="captureTable">

                <h5 class="text-center mb-3">
                    Target vs Topup per Region –
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') }}
                </h5>

                <p class="text-center text-muted mb-4">
                    Last Update :
                    <strong>
                        {{ $lastUpdate
                            ? \Carbon\Carbon::parse($lastUpdate)->format('d M Y')
                            : '-' }}
                    </strong>
                </p>

                <div class="table-responsive">
                    <table id="regionTable" class="table table-bordered table-striped">
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
                            @foreach ($data as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row['region'] }}</td>
                                    <td>{{ $row['pic'] }}</td>
                                    <td class="text-end">{{ number_format($row['target'],0,',','.') }}</td>
                                    <td class="text-end">{{ number_format($row['topup'],0,',','.') }}</td>
                                    <td class="text-center">
                                        <span class="badge
                                            {{ $row['percentage'] >= 90 ? 'bg-success' : ($row['percentage'] >= 70 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $row['percentage'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot class="fw-bold table-light">
                            <tr>
                                <td colspan="3" class="text-center">TOTAL</td>
                                <td class="text-end">
                                    {{ number_format(collect($data)->sum('target'),0,',','.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format(collect($data)->sum('topup'),0,',','.') }}
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

            <div class="text-center mt-4">
                <button id="btnSaveImage" class="btn btn-primary btn-lg">
                    📸 Save Table as Image
                </button>
            </div>

        </div>
    </div>

</div>

{{-- ================= JS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const rawData = @json($data);

    new Chart(document.getElementById('regionBarChart'), {
        type: 'bar',
        data: {
            labels: rawData.map(r => r.region),
            datasets: [
                { label: 'Target', data: rawData.map(r => r.target) },
                { label: 'Topup', data: rawData.map(r => r.topup) }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            scales: {
                x: {
                    ticks: {
                        callback: v => new Intl.NumberFormat('id-ID').format(v)
                    }
                }
            }
        }
    });

    document.getElementById('btnSaveImage').addEventListener('click', function () {
        html2canvas(document.getElementById('captureTable'), { scale: 2 })
            .then(canvas => {
                const link = document.createElement('a');
                link.download = 'target-vs-topup-region.png';
                link.href = canvas.toDataURL();
                link.click();
            });
    });

});
</script>
@endsection
