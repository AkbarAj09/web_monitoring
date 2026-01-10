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
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th rowspan="2" style="vertical-align: middle; text-align: center;">Tanggal</th>
                                <th colspan="8" class="text-center" style="background-color: #f8d7da;">Source_combined / total_settlement_klien / user_id</th>
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
                                <th></th>
                                <th class="text-center">total_settle...</th>
                                <th class="text-center">user_id</th>
                                <th class="text-center">total_settle...</th>
                                <th class="text-center">user_id</th>
                                <th class="text-center">total_settle...</th>
                                <th class="text-center">user_id</th>
                                <th class="text-center">user</th>
                                <th class="text-center">total_settle...</th>
                                <th class="text-center">Total keseluruhan</th>
                                <th class="text-center">user_id</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $dummyData = [
                                    ['date' => '9 Jan 2026', 'mitra_sbp_settle' => '21.098.000', 'mitra_sbp_user' => 4, 'canvasser_settle' => '62.049.000', 'canvasser_user' => 21, 'self_service_settle' => '610.500', 'self_service_user' => 2, 'agency_user' => '', 'agency_settle' => '555.000', 'total' => '84.312.500', 'total_user' => 28],
                                    ['date' => '8 Jan 2026', 'mitra_sbp_settle' => '7.360.000', 'mitra_sbp_user' => 3, 'canvasser_settle' => '8.103.150', 'canvasser_user' => 10, 'self_service_settle' => '854.700', 'self_service_user' => 3, 'agency_user' => '', 'agency_settle' => '-', 'total' => '16.317.850', 'total_user' => 16],
                                    ['date' => '7 Jan 2026', 'mitra_sbp_settle' => '44.630.000', 'mitra_sbp_user' => 11, 'canvasser_settle' => '38.406.000', 'canvasser_user' => 11, 'self_service_settle' => '55.500', 'self_service_user' => 1, 'agency_user' => '', 'agency_settle' => '-', 'total' => '83.091.500', 'total_user' => 23],
                                    ['date' => '6 Jan 2026', 'mitra_sbp_settle' => '56.288.587', 'mitra_sbp_user' => 14, 'canvasser_settle' => '4.707.950', 'canvasser_user' => 9, 'self_service_settle' => '55.500', 'self_service_user' => 1, 'agency_user' => '', 'agency_settle' => '-', 'total' => '61.052.037', 'total_user' => 24],
                                    ['date' => '5 Jan 2026', 'mitra_sbp_settle' => '47.896.149', 'mitra_sbp_user' => 11, 'canvasser_settle' => '6.050.280', 'canvasser_user' => 9, 'self_service_settle' => '1.598.500', 'self_service_user' => 6, 'agency_user' => '', 'agency_settle' => '-', 'total' => '55.544.929', 'total_user' => 26],
                                    ['date' => '4 Jan 2026', 'mitra_sbp_settle' => '-', 'mitra_sbp_user' => '', 'canvasser_settle' => '555.000', 'canvasser_user' => 1, 'self_service_settle' => '55.500', 'self_service_user' => 1, 'agency_user' => '', 'agency_settle' => '-', 'total' => '610.500', 'total_user' => 2],
                                    ['date' => '3 Jan 2026', 'mitra_sbp_settle' => '-', 'mitra_sbp_user' => '', 'canvasser_settle' => '610.500', 'canvasser_user' => 2, 'self_service_settle' => '-', 'self_service_user' => '', 'agency_user' => '', 'agency_settle' => '-', 'total' => '610.500', 'total_user' => 2],
                                    ['date' => '2 Jan 2026', 'mitra_sbp_settle' => '111.000', 'mitra_sbp_user' => 1, 'canvasser_settle' => '2.358.378', 'canvasser_user' => 8, 'self_service_settle' => '5.550.000', 'self_service_user' => 1, 'agency_user' => '', 'agency_settle' => '-', 'total' => '8.019.378', 'total_user' => 10],
                                    ['date' => '1 Jan 2026', 'mitra_sbp_settle' => '277.500', 'mitra_sbp_user' => 1, 'canvasser_settle' => '-', 'canvasser_user' => '', 'self_service_settle' => '555.000', 'self_service_user' => 1, 'agency_user' => '', 'agency_settle' => '-', 'total' => '832.500', 'total_user' => 2],
                                ];
                            @endphp
                            
                            @foreach($dummyData as $row)
                            <tr>
                                <td class="font-weight-bold">{{ $row['date'] }}</td>
                                <td class="text-right">{{ $row['mitra_sbp_settle'] }}</td>
                                <td class="text-center">{{ $row['mitra_sbp_user'] }}</td>
                                <td class="text-right">{{ $row['canvasser_settle'] }}</td>
                                <td class="text-center">{{ $row['canvasser_user'] }}</td>
                                <td class="text-right">{{ $row['self_service_settle'] }}</td>
                                <td class="text-center">{{ $row['self_service_user'] }}</td>
                                <td class="text-center">{{ $row['agency_user'] }}</td>
                                <td class="text-right">{{ $row['agency_settle'] }}</td>
                                <td class="text-right font-weight-bold">{{ $row['total'] }}</td>
                                <td class="text-center font-weight-bold">{{ $row['total_user'] }}</td>
                            </tr>
                            @endforeach
                            
                            <tr class="table-info font-weight-bold">
                                <td>Total keselur...</td>
                                <td class="text-right">177.661.236</td>
                                <td class="text-center">42</td>
                                <td class="text-right">122.840.258</td>
                                <td class="text-center">64</td>
                                <td class="text-right">9.335.200</td>
                                <td class="text-center">15</td>
                                <td class="text-center"></td>
                                <td class="text-right">555.000</td>
                                <td class="text-right">310.391.694</td>
                                <td class="text-center">122</td>
                            </tr>
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