@extends('sidebar')

@section('title', 'Log Login')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Log Login</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Log Login</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data User Login</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filter Section -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="start_date">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                            <div class="col-md-3">
                                <label for="role_filter">Role</label>
                                <select class="form-control" id="role_filter" name="role_filter">
                                    <option value="all">Semua Role</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Tsel">Tsel</option>
                                    <option value="cvsr">Canvasser</option>
                                    <option value="Treg">Treg</option>
                                    <option value="TL">TL</option>
                                    <option value="User">User</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-primary btn-block" id="filter_btn">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- DataTable -->
                        <div class="table-responsive">
                            <table id="loglogin-table" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Last Access</th>
                                        <th>Status</th>
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
    </div>
</section>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        var table = $('#loglogin-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('loglogin.data') }}",
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.role = $('#role_filter').val();
                }
            },
            columns: [
                {
                    data: null,
                    name: 'no',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'tgl',
                    name: 'tgl'
                },
                {
                    data: 'nama',
                    name: 'nama'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'role',
                    name: 'role',
                    render: function(data) {
                        var badge = 'badge-secondary';
                        if (data === 'Admin') badge = 'badge-warning';
                        else if (data === 'Tsel') badge = 'badge-success';
                        else if (data === 'cvsr') badge = 'badge-primary';
                        else if (data === 'Treg') badge = 'badge-info';
                        return '<span class="badge ' + badge + '">' + data + '</span>';
                    }
                },
                {
                    data: 'updated_at',
                    name: 'updated_at'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [
                [1, 'desc'],
                [5, 'desc']
            ],
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            responsive: true,
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Data tidak ditemukan",
                info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                infoEmpty: "Tidak ada data yang tersedia",
                infoFiltered: "(difilter dari _MAX_ total data)",
                search: "Cari:",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        // Filter button click
        $('#filter_btn').on('click', function() {
            table.ajax.reload();
        });

        // Enter key on filter inputs
        $('#start_date, #end_date, #role_filter').on('keypress', function(e) {
            if (e.which === 13) {
                table.ajax.reload();
            }
        });

        // Role filter change
        $('#role_filter').on('change', function() {
            table.ajax.reload();
        });
    });
</script>
@endsection
