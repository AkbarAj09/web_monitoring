@extends('master')
@section('title') Dashboard @endsection
@section('css')

<style>
    .btn-ref {
        position: fixed;
        top: 50px;
        left: 1000px;
    }

    #loading-overlay {

        position: fixed;

        top: 0;

        left: 0;

        width: 100%;

        height: 100%;

        background: rgba(0, 0, 0, 0.7);

        z-index: 9999;

        display: none;

    }

    /* Dashboard Cards */
    .card {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.2);
    }

    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }

    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }

    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }

    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }

    .border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }

    .border-left-secondary {
        border-left: 0.25rem solid #858796 !important;
    }

    .border-left-dark {
        border-left: 0.25rem solid #5a5c69 !important;
    }

    .bg-gradient-primary {
        background: linear-gradient(87deg, #4e73df 0, #224abe 100%);
    }

    .bg-gradient-success {
        background: linear-gradient(87deg, #1cc88a 0, #169b6b 100%);
    }

    .bg-gradient-info {
        background: linear-gradient(87deg, #36b9cc 0, #258391 100%);
    }

    .bg-gradient-danger {
        background: linear-gradient(87deg, #e74a3b 0, #be2617 100%);
    }

    .bg-gradient-warning {
        background: linear-gradient(87deg, #f6c23e 0, #dda20a 100%);
    }

    .text-xs {
        font-size: 0.75rem;
    }

    .text-gray-800 {
        color: #5a5c69 !important;
    }

    .text-gray-300 {
        color: #dddfeb !important;
    }

    .text-white-50 {
        color: rgba(255, 255, 255, 0.5) !important;
    }

    .bg-white-50 {
        background-color: rgba(255, 255, 255, 0.5) !important;
    }

    .badge-purple {
        background-color: #6f42c1;
        color: white;
    }

    .btn-group-vertical .btn {
        border-radius: 0.25rem;
        margin-bottom: 0.25rem;
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translate3d(0, 40px, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    .card {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .h3, .h4, .h5 {
            font-size: 1.2rem;
        }
        
        .fa-2x {
            font-size: 1.5em;
        }
        
        .btn-group-vertical .btn {
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
        }
    }

    .table {
        background-color: #f9f9f9;
        border-radius: 8px;
        overflow: hidden;
        width: 100%;
        max-width: 100%;
        margin-top: 15px;
        border: 0.5px solid #ccc;
        table-layout: auto;

        /* Allow dynamic column sizing */
    }

    .table th,
    .table td {
        padding: 8px !important;
        font-size: 16px;
        border: 0.5px solid #ccc;
        color: #313131;
        text-align: center;
    }

    .table th {
        font-weight: bold;
        text-align: center;
        /* Align text to the left */
    }


    .table tbody tr {
        transition: background-color 0.3s;
    }

    .table tbody tr:hover {
        background-color: #e2e2e2;
    }

    .table tbody tr:nth-child(odd) {
        background-color: #f2f2f2;
        /* Odd row background color */
    }

    .table tbody tr:nth-child(even) {
        background-color: #ffffff;
        /* Even row background color */
    }

    @media (max-width: 768px) {

        .table th,
        .table td {
            font-size: 12px;
        }
    }
</style>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<!-- Dashboard Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary">
            <div class="card-body text-white text-center">
                <h2><i class="fas fa-tachometer-alt"></i> Dashboard MyAds Monitoring</h2>
                <p class="mb-0">Selamat datang di sistem monitoring MyAds Telkomsel</p>
            </div>
        </div>
    </div>
</div>

<!-- Daily Topup Table -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-gradient-danger text-white">
                <h4 class="mb-0"><i class="fas fa-chart-bar"></i> Daily Topup / Channel</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm w-100 table-bordered table-hover" id="dailyTopupTable" style="font-size: 12px;">
                        <thead class="thead-light">
                            <tr>
                                <th rowspan="3" style="vertical-align: middle; text-align: center; background-color: #f8f9fa;">Tanggal</th>
                                <th colspan="10" class="text-center" style="background-color: #f8d7da;">Source_combined / total_settlement_klien / user_id</th>
                            </tr>
                            <tr>
                                <th colspan="2" class="text-center" style="background-color: #fff3cd;">Mitra SBP</th>
                                <th colspan="2" class="text-center" style="background-color: #d1ecf1;">Canvasser</th>
                                <th colspan="2" class="text-center" style="background-color: #d4edda;">Self Service</th>
                                <th colspan="2" class="text-center" style="background-color: #e2e3e5;">Agency</th>
                                <th class="text-center" style="background-color: #f8d7da;">Total keseluruhan</th>
                                <th class="text-center" style="background-color: #f8d7da;">user_id</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="background-color: #fff3cd;">Total Settlement</th>
                                <th class="text-center" style="background-color: #fff3cd;">user_id</th>
                                <th class="text-center" style="background-color: #d1ecf1;">Total Settlement</th>
                                <th class="text-center" style="background-color: #d1ecf1;">user_id</th>
                                <th class="text-center" style="background-color: #d4edda;">Total Settlement</th>
                                <th class="text-center" style="background-color: #d4edda;">user_id</th>
                                <th class="text-center" style="background-color: #e2e3e5;">user_id</th>
                                <th class="text-center" style="background-color: #e2e3e5;">Total Settlement</th>
                                <th class="text-center" style="background-color: #f8d7da;">Total</th>
                                <th class="text-center" style="background-color: #f8d7da;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Last Updated Info -->
<div class="row">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body text-center">
                <small class="text-muted">
                    <i class="fas fa-clock"></i> Last updated: {{ now()->format('d F Y, H:i:s') }} WIB
                </small>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    $(document).ready(function() {
        var table = $('#dailyTopupTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            paging: false,
            ajax: {
                url: "{{ route('daily_topup_data') }}",
                type: "GET",
                dataSrc: function(json) {
                    console.log("Response dari server:", json);
                    return json.data || [];
                }
            },
            columns: [
                {
                    data: 'date',
                    render: function(data, type, row) {
                        if (data === 'Total Keseluruhan') {
                            return `<div style="text-align: center; font-weight: bold;">${data}</div>`;
                        }
                        return `<div style="text-align: center;">${data}</div>`;
                    }
                },
                {
                    data: 'mitra_sbp_settle',
                    render: function(data, type, row) {
                        let className = row.date === 'Total Keseluruhan' ? 'font-weight-bold' : '';
                        return `<div style="text-align: right;" class="${className}">${data || '-'}</div>`;
                    }
                },
                {
                    data: 'mitra_sbp_user',
                    render: function(data, type, row) {
                        let className = row.date === 'Total Keseluruhan' ? 'font-weight-bold' : '';
                        return `<div style="text-align: center;" class="${className}">${data || '-'}</div>`;
                    }
                },
                {
                    data: 'canvasser_settle',
                    render: function(data, type, row) {
                        let className = row.date === 'Total Keseluruhan' ? 'font-weight-bold' : '';
                        return `<div style="text-align: right;" class="${className}">${data || '-'}</div>`;
                    }
                },
                {
                    data: 'canvasser_user',
                    render: function(data, type, row) {
                        let className = row.date === 'Total Keseluruhan' ? 'font-weight-bold' : '';
                        return `<div style="text-align: center;" class="${className}">${data || '-'}</div>`;
                    }
                },
                {
                    data: 'self_service_settle',
                    render: function(data, type, row) {
                        let className = row.date === 'Total Keseluruhan' ? 'font-weight-bold' : '';
                        return `<div style="text-align: right;" class="${className}">${data || '-'}</div>`;
                    }
                },
                {
                    data: 'self_service_user',
                    render: function(data, type, row) {
                        let className = row.date === 'Total Keseluruhan' ? 'font-weight-bold' : '';
                        return `<div style="text-align: center;" class="${className}">${data || '-'}</div>`;
                    }
                },
                {
                    data: 'agency_user',
                    render: function(data, type, row) {
                        let className = row.date === 'Total Keseluruhan' ? 'font-weight-bold' : '';
                        return `<div style="text-align: center;" class="${className}">${data || '-'}</div>`;
                    }
                },
                {
                    data: 'agency_settle',
                    render: function(data, type, row) {
                        let className = row.date === 'Total Keseluruhan' ? 'font-weight-bold' : '';
                        return `<div style="text-align: right;" class="${className}">${data || '-'}</div>`;
                    }
                },
                {
                    data: 'total',
                    render: function(data, type, row) {
                        return `<div style="text-align: right; font-weight: bold;">${data || '-'}</div>`;
                    }
                },
                {
                    data: 'total_user',
                    render: function(data, type, row) {
                        return `<div style="text-align: center; font-weight: bold;">${data || '-'}</div>`;
                    }
                }
            ],
            rowCallback: function(row, data) {
                if (data.date === 'Total Keseluruhan') {
                    $(row).addClass('table-info');
                }
            }
        });

        $('#dailyTopupTable').on('error.dt', function(e, settings, techNote, message) {
            console.log("DataTables Error:", message);
        });
    });
</script>
@endsection