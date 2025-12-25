@extends('master')
@section('title') Leads Master @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet"/>
<style>
    .card-title { font-weight: bold; }
    .form-group label { font-weight: 600; }
    .select2-container .select2-selection--single {
        height: 35px !important;
        padding: 8px 12px;
        border: 1px solid #ced4da !important;
        border-radius: 6px !important;
        display: flex;
        align-items: center;
        font-size: 15px;
        background-color: #fff;
    }
    .d-flex.gap-3 > label {
        cursor: pointer;
        user-select: none;
    }
    .text-danger { font-size: 13px; }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="font-weight-bold">Leads Master</h4>
    </div>

    <div class="card-body">

        {{-- FILTER --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Canvasser</label>
                <select id="filter_canvasser" class="form-control select2">
                    <option value="">-- Semua --</option>
                    @foreach($canvassers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Nama Perusahaan</label>
                <input type="text" id="filter_company" class="form-control" placeholder="Cari perusahaan">
            </div>

            <div class="col-md-3">
                <label>Email</label>
                <input type="text" id="filter_email" class="form-control" placeholder="Cari email">
            </div>

            <div class="col-md-3">
                <label>Lead Source</label>
                <select id="filter_source" class="form-control select2">
                    <option value="">-- Semua --</option>
                    @foreach($sources as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
<div class="d-flex justify-content-end mb-3"> <a href="{{ route('leads-master.create') }}" class="btn btn-info" id="btn-add-lead"> <i class="fas fa-plus"></i> Tambah Leads </a> </div>
        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="leadsMasterTable">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Canvasser</th>
                        <th>Perusahaan</th>
                        <th>No HP</th>
                        <th>Email</th>
                        <th>Lead Source</th>
                        <th>Nama</th>
                        <th>Sector</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(function () {

    $('.select2').select2({ width: '100%' });

    let table = $('#leadsMasterTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('leads-master.data') }}",
            data: function (d) {
                d.canvasser = $('#filter_canvasser').val();
                d.company   = $('#filter_company').val();
                d.email     = $('#filter_email').val();
                d.source    = $('#filter_source').val();
            }
        },
        columns: [
            { data: 'user_name' },
            { data: 'company_name' },
            { data: 'mobile_phone' },
            { data: 'email' },
            { data: 'source_name' },
            { data: 'nama' },
            { data: 'sector_name' },
            { data: 'status', orderable:false, searchable:false },
            { data: 'remarks' },
            { data: 'aksi', orderable:false, searchable:false }
        ]
    });

    $('#filter_canvasser, #filter_source').on('change', function () {
        table.ajax.reload();
    });

    $('#filter_company, #filter_email').on('keyup', function () {
        table.ajax.reload();
    });

});
</script>
@endsection
