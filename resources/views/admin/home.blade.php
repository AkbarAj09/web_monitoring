@extends('master')
@section('title') Dashboard @endsection
@section('css')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    .btn-ref {
        position: fixed;
        top: 50px;
        left: 1000px;
    }

    #chart1, #chart2, #chart3 {
        min-height: 400px;
        max-height: 500px;
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

        .h3,
        .h4,
        .h5 {
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

    /* Custom Select2 Styling */
    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        min-height: 38px;
        padding: 0.375rem 0.75rem;
        font-size: 0.9rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .select2-container--bootstrap-5 .select2-selection:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding-left: 0;
        color: #212529;
        line-height: 1.5;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 8px;
    }

    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #0d6efd;
        color: #fff;
    }

    .select2-container--bootstrap-5 .select2-results__option--selected {
        background-color: #e7f1ff;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .form-label i {
        margin-right: 5px;
        color: #6c757d;
    }

    /* Filter Card Styling */
    .filter-section {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 0.375rem;
        margin-bottom: 1rem;
    }

    .quick-nav-section {
        background: #fff;
        padding: 1rem;
        border-radius: 0.375rem;
        border: 1px solid #dee2e6;
    }

    /* Button Styling */
    .btn-group-custom .btn {
        margin-bottom: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-group-custom .btn:hover {
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    /* Input Month Styling */
    input[type="month"] {
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        transition: all 0.15s ease-in-out;
    }

    input[type="month"]:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Divider */
    .divider {
        height: 1px;
        background: linear-gradient(to right, transparent, #dee2e6, transparent);
        margin: 1rem 0;
    }

    /* Quick Navigation in Header */
    .btn-light {
        transition: all 0.3s ease;
        padding: 0.5rem 0.25rem;
        font-weight: 500;
    }

    .btn-light:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .btn-light i {
        font-size: 1.2rem;
    }

    .btn-light small {
        font-size: 0.75rem;
        display: block;
    }

    @media (max-width: 768px) {
        .btn-light i {
            font-size: 1rem;
        }
        .btn-light small {
            font-size: 0.65rem;
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
            <div class="card-body text-white">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        <h2 class="mb-2"><i class="fas fa-tachometer-alt"></i> Dashboard MyAds Monitoring</h2>
                        <p class="mb-0">Selamat datang di sistem monitoring MyAds Telkomsel</p>
                    </div>
                    <div class="col-md-6">
                        <div class="row g-2">
                            <div class="col-12 mb-2">
                                <small class="d-block text-white-50">
                                    <i class="fas fa-bolt"></i> Quick Navigation
                                </small>
                            </div>
                            <div class="col-md-4 col-4">
                                <button type="button" class="btn btn-light btn-sm w-100" onclick="scrollToSection('chart1Card')">
                                    <i class="fas fa-chart-bar d-block mb-1"></i>
                                    <small>Charts</small>
                                </button>
                            </div>
                            <div class="col-md-4 col-4">
                                <button type="button" class="btn btn-light btn-sm w-100" onclick="scrollToSection('canvaserTableCard')">
                                    <i class="fas fa-table d-block mb-1"></i>
                                    <small>Canvasser</small>
                                </button>
                            </div>
                            <div class="col-md-4 col-4">
                                <button type="button" class="btn btn-light btn-sm w-100" onclick="scrollToSection('dailyTopupTableCard')">
                                    <i class="fas fa-chart-line d-block mb-1"></i>
                                    <small>Daily Topup</small>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Target vs ACV (Juta Rp)</h6>
            </div>
            <div class="card-body">
                <canvas id="chart3" style="height: 400px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

    <!-- Filter & Shortcut Card
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-gradient-warning text-white">
                <h5 class="mb-0"><i class="fas fa-filter"></i> Filter & Quick Navigation</h5>
            </div>
            <div class="card-body">
                <div class="filter-section">
                    <div class="mb-3">
                        <label for="filterArea" class="form-label">
                            <i class="fas fa-map-marked-alt text-primary"></i> Area
                        </label>
                        <select class="form-select select2-custom" id="filterArea" data-placeholder="Pilih Area">
                            <option value="">Semua Area</option>
                            <option value="AREA 1">AREA 1</option>
                            <option value="AREA 2">AREA 2</option>
                            <option value="AREA 3" selected>AREA 3</option>
                            <option value="AREA 4">AREA 4</option>
                            <option value="AREA 5">AREA 5</option>
                            <option value="AREA 6">AREA 6</option>
                            <option value="AREA 7">AREA 7</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="filterRegional" class="form-label">
                            <i class="fas fa-map-marker-alt text-success"></i> Regional
                        </label>
                        <select class="form-select select2-custom" id="filterRegional" data-placeholder="Pilih Regional">
                            <option value="">Semua Regional</option>
                            <option value="JAKARTA">JAKARTA</option>
                            <option value="BANDUNG">BANDUNG</option>
                            <option value="SEMARANG">SEMARANG</option>
                            <option value="SURABAYA">SURABAYA</option>
                            <option value="MEDAN">MEDAN</option>
                            <option value="MAKASSAR">MAKASSAR</option>
                            <option value="DENPASAR">DENPASAR</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="filterPeriode" class="form-label">
                            <i class="fas fa-calendar-alt text-info"></i> Periode
                        </label>
                        <input type="month" class="form-control" id="filterPeriode" value="{{ now()->format('Y-m') }}">
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-primary btn-sm w-100" id="applyFilter">
                                <i class="fas fa-check"></i> Terapkan
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="resetFilter">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->

<!-- Report Canvaser All Region -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card" id="canvaserTableCard">
            <div class="card-header bg-gradient-success text-white">
                <h4 class="mb-0"><i class="fas fa-table"></i> Report Canvaser All Region</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm w-100 table-bordered table-hover" id="regionalTable" style="font-size: 11px;">
                        <thead class="thead-light">
                            <tr>
                                <th colspan="15" class="text-center" style="background-color: #d1ecf1;">Data Bulan Berjalan: {{ now()->format('Y-m') }}</th>
                            </tr>
                            <tr>
                                <th rowspan="3" style="vertical-align: middle; text-align: center; background-color: #f8f9fa;">No</th>
                                <th rowspan="3" style="vertical-align: middle; text-align: center; background-color: #f8f9fa;">Canvaser Name</th>
                            </tr>
                            <tr>
                                <th colspan="2" class="text-center" style="background-color: #cfe2ff;">Data Prospect (Leads & Eksisting Akun)</th>
                                <th colspan="2" class="text-center" style="background-color: #d1e7dd;">Deal (New Akun & Top UP)</th>
                                <th colspan="2" style="vertical-align: middle; text-align: center; background-color: #f8d7da;">Top Up (Rp.)</th>
                                <th colspan="4" style="vertical-align: middle; text-align: center; background-color: #fff3cd;">Target & Achievement</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="background-color: #cfe2ff;">Leads</th>
                                <th class="text-center" style="background-color: #cfe2ff;">Eksisting Akun</th>
                                <th class="text-center" style="background-color: #d1e7dd;">New Akun</th>
                                <th class="text-center" style="background-color: #d1e7dd;">Eksisting Akun Top UP</th>
                                <th class="text-center" style="background-color: #f8d7da;">New Akun(Rp.)</th>
                                <th class="text-center" style="background-color: #f8d7da;">Eksisting Akun(Rp.)</th>
                                <th class="text-center" style="background-color: #fff3cd;">Target (Rp)</th>
                                <th class="text-center" style="background-color: #fff3cd;">Achievement (%)</th>
                                <th class="text-center" style="background-color: #fff3cd;">Gap (Rp)</th>
                                <th class="text-center" style="background-color: #fff3cd;">Gap Daily (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="14" class="text-center">Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Topup Table -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card" id="dailyTopupTableCard">
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Register plugin datalabels
    Chart.register(ChartDataLabels);

    $(document).ready(function() {
        // Initialize Select2 for dropdowns
        $('.select2-custom').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('.card-body'),
            allowClear: true,
            placeholder: function(){
                return $(this).data('placeholder');
            }
        });
        // Load Chart Data untuk Regional
        loadRegionalChart();

        // Filter functionality
        $('#applyFilter').on('click', function() {
            const area = $('#filterArea').val();
            const regional = $('#filterRegional').val();
            const periode = $('#filterPeriode').val();
            
            console.log('Filter applied:', { area, regional, periode });
            
            // Reload tables dengan filter
            regionalTable.ajax.reload();
            $('#dailyTopupTable').DataTable().ajax.reload();
            
            // Show notification
            alert('Filter diterapkan: ' + 
                (area ? 'Area: ' + area : '') + 
                (regional ? ', Regional: ' + regional : '') + 
                (periode ? ', Periode: ' + periode : '')
            );
        });

        $('#resetFilter').on('click', function() {
            $('#filterArea').val('AREA 3').trigger('change');
            $('#filterRegional').val('').trigger('change');
            $('#filterPeriode').val('{{ now()->format('Y-m') }}').trigger('change');
            
            // Reload tables
            regionalTable.ajax.reload();
            $('#dailyTopupTable').DataTable().ajax.reload();
            
            console.log('Filter reset');
        });

        // DataTable untuk Regional Data
        var regionalTable = $('#regionalTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            paging: false,
            searching: false,
            ajax: {
                url: "{{ route('regional_data') }}",
                type: "GET",
                dataSrc: function(json) {
                    console.log("Regional Data:", json);
                    return json.data || [];
                }
            },
            columns: [{
                    data: 'no',
                    className: 'text-center',
                    render: function(data, type, row, meta) {
                        return `<div style="text-align: center;">${meta.row + 1}</div>`;
                    }
                },
                {
                    data: 'canvaser_name',
                    className: 'text-center',
                    render: function(data) {
                        return `<div style="text-align: center;">${data}</div>`;
                    }
                },
                {
                    data: 'leads',
                    className: 'text-center',
                    render: function(data) {
                        return `<div style="text-align: center;">${parseInt(data).toLocaleString('id-ID')}</div>`;
                    }
                },
                {
                    data: 'existing_akun',
                    className: 'text-center',
                    render: function(data) {
                        return `<div style="text-align: center;">${parseInt(data).toLocaleString('id-ID')}</div>`;
                    }
                },
                {
                    data: 'new_akun',
                    className: 'text-center',
                    render: function(data) {
                        return `<div style="text-align: center;">${parseInt(data).toLocaleString('id-ID')}</div>`;
                    }
                },
                {
                    data: 'top_up',
                    className: 'text-center',
                    render: function(data) {
                        return `<div style="text-align: center;">${parseInt(data).toLocaleString('id-ID')}</div>`;
                    }
                },
                {
                    data: 'top_up_new_akun_rp',
                    className: 'text-center',
                    render: function(data) {
                        return `<div style="text-align: center;">${data}</div>`;
                    }
                },
                {
                    data: 'top_up_existing_akun_rp',
                    className: 'text-center',
                    render: function(data) {
                        return `<div style="text-align: center;">${data}</div>`;
                    }
                },
                {
                    data: 'target',
                    className: 'text-center',
                    render: function(data) {
                        return `<div style="text-align: center; font-weight: bold; color: #0d6efd;">${data}</div>`;
                    }
                },
                {
                    data: 'achievement_percent',
                    className: 'text-center',
                    render: function(data) {
                        let percent = parseFloat(data.replace(',', '.').replace('%', ''));
                        let color = percent >= 100 ? '#28a745' : (percent >= 75 ? '#ffc107' : '#dc3545');
                        return `<div style="text-align: center; font-weight: bold; color: ${color};">${data}</div>`;
                    }
                },
                {
                    data: 'gap',
                    className: 'text-center',
                    render: function(data) {
                        return `<div style="text-align: center; color: #dc3545;">${data}</div>`;
                    }
                },
                {
                    data: 'gap_daily',
                    className: 'text-center',
                    render: function(data) {
                        return `<div style="text-align: center; color: #fd7e14; font-weight: bold;">${data}</div>`;
                    }
                }
            ]
        });

        // DataTable untuk Daily Topup
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
            columns: [{
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
                        backgroundColor: '#EE3124', // Merah Telkomsel
                    }, {
                        label: 'Deal: New Akun',
                        data: canvassers.map(c => c.new_akun),
                        backgroundColor: '#F26522', // Orange
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
                        backgroundColor: '#FF6B35', // Orange Terang
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
                        backgroundColor: '#EE3124', // Merah Telkomsel
                    }, {
                        label: 'ACV (Juta Rp)',
                        data: canvassers.map(c => c.acv / 1000000),
                        backgroundColor: '#D94E1F', // Orange Gelap
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

    // Scroll to section function
    function scrollToSection(sectionId) {
        const element = document.getElementById(sectionId);
        if (element) {
            element.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
            
            // Add highlight effect
            const card = element.closest('.card');
            if (card) {
                card.style.transition = 'all 0.3s';
                card.style.boxShadow = '0 0 20px rgba(0,123,255,0.5)';
                setTimeout(() => {
                    card.style.boxShadow = '';
                }, 2000);
            }
        }
    }
</script>
@endsection