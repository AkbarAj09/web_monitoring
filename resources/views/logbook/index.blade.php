@extends('master')
@section('title') Logbook @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet"/>
<style>
    .card-title { font-weight: bold; }
    .select2-container .select2-selection--single {
        height: 35px !important;
        padding: 6px 10px;
    }
</style>
@endsection

@section('content')

{{-- FILTER BAR --}}
<div class="row mb-3 align-items-end">
    <div class="col-md-6"></div>

    <div class="col-md-6 text-end">
        <div class="d-flex justify-content-end gap-2">

            {{-- Regional --}}
            <select id="filter_regional" class="form-control" style="max-width: 200px">
                <option value="">All Regional</option>
                @foreach($regionals as $regional)
                    <option value="{{ $regional }}">{{ $regional }}</option>
                @endforeach
            </select>

            {{-- Date --}}
            <input type="month" id="month" class="form-control" style="max-width: 160px"
       value="{{ now()->format('Y-m') }}">
            {{-- <input type="date" id="end_date" class="form-control" style="max-width: 160px"> --}}

            <button id="btnRefresh" class="btn btn-secondary">
                Refresh
            </button>

            <button id="btnExport" class="btn btn-success">
                <i class="fa fa-file-excel"></i> XLS
            </button>

        </div>
    </div>
</div>

{{-- TABLE --}}
<div class="card">
    <div class="card-header">
        <h4 class="font-weight-bold">Logbook</h4>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered table-sm" id="leadsMasterTable">
            <thead class="bg-dark text-white">
                <tr>
                    <th>Canvasser</th>
                    <th>Regional</th>
                    <th>Nama Perusahaan</th>
                    <th>Akun Myads</th>
                    <th>No HP</th>
                    <th>Tipe Data</th>
                    <th>Tanggal</th>
                    <th>Komitmen</th>
                    <th>Plan Min Topup</th>
                    <th>Realisasi Topup</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(function () {

    let table = $('#leadsMasterTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('logbook.data') }}",
            data: function (d) {
                d.regional   = $('#filter_regional').val();
                d.start_date = $('#start_date').val();
                d.end_date   = $('#end_date').val();
            }
        },
        columns: [
            { data: 'user_name' },
            { data: 'regional' },
            { data: 'company_name' },
            { data: 'myads_account' },
            { data: 'mobile_phone' },
            { data: 'data_type' },
            { data: 'created_at' },
            { data: 'komitmen' },
            { data: 'plan_min_topup' },
            { data: 'total_settlement_klien' },
        ]
    });

    // Refresh
    $('#btnRefresh').on('click', function () {
        table.ajax.reload(null, false);
    });

    // Export
    $('#btnExport').on('click', function () {
        let params = {
            regional: $('#filter_regional').val(),
            month: $('#month').val(),
            // end_date: $('#end_date').val()
        };

        window.location =
            "{{ route('leads-master.export') }}?" + $.param(params);
    });

});
</script>
@endsection
