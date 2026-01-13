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
        background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
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
    <div class="col-12 text-right">
        <button id="btn-export" class="btn btn-success">
            <i class="fas fa-file-excel mr-2"></i>Export Excel
        </button>
    </div>
</div>

<!-- Table Section -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary">
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

@section('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#reportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('panenpoin.report-data') }}",
                dataSrc: function(json) {
                    console.log("Response dari server:", json);
                    return json.data || [];
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

        // Export button click
        $('#btn-export').click(function() {
            window.location.href = "{{ route('panenpoin.export') }}";
        });
    });
</script>
@endsection