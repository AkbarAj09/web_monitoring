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
    {{-- <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                Target vs Topup per Region
                ({{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') }})
            </h5>
        </div>
        <div class="card-body" style="height: 400px">
            <canvas id="regionBarChart" height="100"></canvas>
        </div>
    </div> --}}
    <!-- Bar Chart Section -->
    <div class="row mb-4">
        <!-- Card 1: Prospect Leads vs Deal New Akun -->
        <div class="col-md-4">
            <div class="card" id="chart1Card">
                <div class="card-header bg-gradient-info text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Prospect Leads vs Deal New Akun</h6>
                </div>
                <div class="card-body">
                    <canvas id="chart1" style="height: 400px; width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- Card 2: Prospect Existing Akun vs Deal Top Up -->
        <div class="col-md-4">
            <div class="card" id="chart2Card">
                <div class="card-header bg-gradient-success text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Prospect Existing Akun vs Deal Top Up</h6>
                </div>
                <div class="card-body">
                    <canvas id="chart2" style="height: 400px; width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- Card 3: Target vs ACV -->
        <div class="col-md-4">
            <div class="card" id="chart3Card">
                <div class="card-header bg-gradient-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Target vs ACH (Juta Rp)</h6>
                </div>
                <div class="card-body">
                    <canvas id="chart3" style="height: 400px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card">
        <div class="card-body">

            <div id="captureTable">

                <h5 class="text-center mb-3">
                    Report Powerhouse –
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
{{-- <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script> --}}

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // const rawData = @json($data);

    // new Chart(document.getElementById('regionBarChart'), {
    //     type: 'bar',
    //     data: {
    //         labels: rawData.map(r => r.region),
    //         datasets: [
    //             { label: 'Target', data: rawData.map(r => r.target) },
    //             { label: 'Topup', data: rawData.map(r => r.topup) }
    //         ]
    //     },
    //     options: {
    //         indexAxis: 'y',
    //         responsive: true,
    //         scales: {
    //             x: {
    //                 ticks: {
    //                     callback: v => new Intl.NumberFormat('id-ID').format(v)
    //                 }
    //             }
    //         }
    //     }
    // });

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

    $(document).ready(function() {
        
        loadRegionalChart();
        // Function untuk load Regional Chart
        function loadRegionalChart() {
            $.ajax({
                url: "{{ route('regional_chart_data') }}",
                type: "GET",
                success: function(response) {
                    console.log("Chart Data:", response);
                    renderRegionalChart(response);
                },
                error: function(xhr, status, error) {
                    console.error("Error loading chart data:", error);
                }
            });
        }

        // Function untuk render Chart
        function renderRegionalChart(data) {
            const canvassers = data.canvassers || [];
            const labels = canvassers.map(c => c.name);

            // Chart 1: Prospect New Leads vs Deal New Akun
            const ctx1 = document.getElementById('chart1').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Prospect: New Leads',
                        data: canvassers.map(c => c.new_leads),
                        backgroundColor: '#ff3324', // Merah Telkomsel
                    }, {
                        label: 'Deal: New Akun',
                        data: canvassers.map(c => c.new_akun),
                        backgroundColor: '#0048a0', // Orange
                    }]
                },
                options: getChartOptions('count')
            });

            // Chart 2: Prospect Existing Akun vs Deal Top Up Existing Akun
            const ctx2 = document.getElementById('chart2').getContext('2d');
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Prospect: Existing Akun',
                        data: canvassers.map(c => c.existing_akun_count),
                        backgroundColor: '#C8102E', // Merah Tua
                    }, {
                        label: 'Deal: Top Up Count',
                        data: canvassers.map(c => c.top_up_existing_akun_count),
                        backgroundColor: '#121ded', // Orange Terang
                    }]
                },
                options: getChartOptions('count')
            });

            // Chart 3: Target vs ACV (dalam jutaan)
            const ctx3 = document.getElementById('chart3').getContext('2d');
            new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Target (Juta Rp)',
                        data: canvassers.map(c => c.target / 1000000),
                        backgroundColor: '#fe2718', // Merah Telkomsel
                    }, {
                        label: 'ACV (Juta Rp)',
                        data: canvassers.map(c => c.acv / 1000000),
                        backgroundColor: '#1f54d9', // Orange Gelap
                    }]
                },
                options: getChartOptions('currency')
            });
        }

        // Helper function untuk chart options
        function getChartOptions(type) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            display: true
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('id-ID');
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                weight: 'bold',
                                size: 9
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 10
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (type === 'currency') {
                                    label += context.parsed.x.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' Jt';
                                } else {
                                    label += context.parsed.x.toLocaleString('id-ID');
                                }
                                return label;
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        offset: 2,
                        font: {
                            weight: 'bold',
                            size: 8
                        },
                        color: '#000',
                        formatter: function(value) {
                            if (value === 0) return '';
                            if (type === 'currency') {
                                return value.toLocaleString('id-ID', {minimumFractionDigits: 1, maximumFractionDigits: 1});
                            }
                            return value.toLocaleString('id-ID');
                        }
                    }
                }
            };
        }
    });
</script>
@endsection
