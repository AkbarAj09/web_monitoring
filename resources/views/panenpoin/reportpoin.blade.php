@extends('master')
@section('title') Report Panen Poin @endsection

@section('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

<style>
    .card {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
    }

    .table thead th {
        background: linear-gradient(180deg, #df514e 10%, #be2222 100%);
        color: white;
        font-weight: 600;
        border: none;
    }

    .badge-poin {
        font-size: 1.1em;
        padding: 8px 15px;
    }

    .filter-section {
        background: #f8f9fc;
        padding: 15px;
        border-radius: 0.35rem;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">
                <i class="fas fa-calendar-alt mr-2 text-primary"></i>
                <strong>Bulan:</strong> 
                <span id="selectedMonth" class="text-danger">{{ $months[array_search(true, array_column($months, 'selected'))]['label'] ?? 'Januari 2026' }}</span>
            </h4>
        </div>
        <div class="d-flex align-items-center">
            <button id="showFilterBtn" class="btn btn-outline-primary" type="button" title="Filter Bulan" style="padding: 6px 12px;">
                <i class="fas fa-filter"></i>
            </button>
            <div id="filterContainer" style="display: none; min-width: 220px; margin-left: 10px;">
                <select id="tanggal" name="tanggal" class="form-control select2"
                    style="background-color: #313131; color: white;">
                    @foreach ($months as $month)
                    <option value="{{ $month['value'] }}" {{ $month['selected'] ? 'selected' : '' }}>
                        {{ $month['label'] }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button id="btn-export" class="btn btn-success ml-2">
                <i class="fas fa-file-excel mr-2"></i>Export Excel
            </button>
        </div>
    </div>
</div>

<!-- Table Section -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-danger">
                <h3 class="card-title text-white">
                    <i class="fas fa-chart-line mr-2"></i>Data Poin Canvasser
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="reportTable" class="table table-bordered table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Canvasser</th>
                                <th>Email Client</th>
                                <th>Nomor HP Client</th>
                                <th>Total Settlement</th>
                                <th>Poin</th>
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

<!-- Info Box -->
<div class="row mt-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Keterangan:</strong> Setiap Rp 250.000 settlement = 1 poin. Data menampilkan bulan berjalan dengan akumulasi poin dari bulan sebelumnya di tahun yang sama.
        </div>
    </div>
</div>

@endsection

@section('js')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        
        // Toggle filter visibility
        $('#showFilterBtn').click(function() {
            $('#filterContainer').toggle();
        });
        
        // Initialize DataTable
        var table = $('#reportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('panenpoin.report-data') }}",
                data: function(d) {
                    d.tanggal = $('#tanggal').val(); // Kirim nilai filter ke server
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'nama_canvasser'
                },
                {
                    data: 'email_client'
                },
                {
                    data: 'nomor_hp_client'
                },
                {
                    data: 'total_settlement',
                    render: function(data) {
                        return 'Rp ' + data;
                    }
                },
                {
                    data: 'poin',
                    render: function(data) {
                        return '<span class="badge badge-success badge-poin">' + data + ' Poin</span>';
                    }
                }
            ],
            order: [
                [5, 'desc']
            ], // Order by poin descending
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
                emptyTable: 'Tidak ada data untuk ditampilkan',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                infoFiltered: '(disaring dari _MAX_ total data)',
                lengthMenu: 'Tampilkan _MENU_ data',
                search: 'Cari:',
                zeroRecords: 'Data tidak ditemukan',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Selanjutnya',
                    previous: 'Sebelumnya'
                }
            },
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "Semua"]
            ]
        });
        
        // Event onchange untuk filter tanggal
        $('#tanggal').on('change', function() {
            // Update label bulan yang ditampilkan
            var selectedText = $('#tanggal option:selected').text();
            $('#selectedMonth').text(selectedText);
            
            // Reload data table
            table.ajax.reload();
        });

        // Export button click
        $('#btn-export').click(function() {
            var tanggal = $('#tanggal').val();
            var exportUrl = "{{ route('panenpoin.export') }}" + "?tanggal=" + tanggal;
            window.location.href = exportUrl;
        });
    });
</script>
@endsection