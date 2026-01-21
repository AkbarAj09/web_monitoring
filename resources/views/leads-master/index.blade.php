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

@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif


<div class="row align-items-end mb-3">

    <!-- Spacer -->
    <div class="col-md-2"></div>

    <!-- Filter Tanggal + Buttons -->
    <div class="col-md-10">
        <div class="d-flex flex-column flex-md-row justify-content-md-end gap-2">

            @if(Auth::user()->role != 'cvsr')
            <select id="filter_canvasser" class="form-control select2 mb-1">
                <option value="">Semua Canvasser</option>
                @foreach($canvassers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            @endif
             @if(Auth::user()->role != 'PH')
            <select id="filter_regional"
                class="form-select form-control w-100 w-md-auto ml-2 mb-1">
                <option value="">All Regional</option>
                @foreach($regionals as $regional)
                    <option value="{{ $regional }}">{{ $regional }}</option>
                @endforeach
            </select>
            @endif
            <input type="date"
                id="start_date"
                class="form-control w-100 w-md-auto ml-2 mb-1">

            <input type="date"
                id="end_date"
                class="form-control w-100 w-md-auto ml-2 mb-1">

            <button id="btnRefresh"
                class="btn btn-secondary w-100 w-md-auto ml-2 mb-1">
                Refresh
            </button>

            <button id="btnExport"
                class="btn btn-success w-100 w-md-auto ml-2 mb-1">
                <i class="fa fa-file-excel"></i> XLS
            </button>

        </div>
    </div>

</div>

<div class="card">
    <div class="card-header">
        <h4 class="font-weight-bold">Data Detail Leads & Akun Myads</h4>
    </div>

    <div class="card-body">

        {{-- FILTER --}}
        {{-- <div class="row mb-3">
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
        </div> --}}
{{-- <div class="d-flex justify-content-end mb-3"> <a href="{{ route('leads-master.create') }}" class="btn btn-info" id="btn-add-lead"> <i class="fas fa-plus"></i> Tambah Leads </a> </div> --}}
        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="leadsMasterTable">
                <thead class="bg-dark text-white">
                    <tr>
                        {{-- <th>Status</th> --}}
                        <th>Canvasser</th>
                        <th>Regional</th>
                        <th>Nama Perusahaan</th>
                        <th>Email</th>
                        <th>No HP</th>
                        <th>Tipe Data</th>
                        <th>Tanggal</th>
                        <th>Total Settlement ({{now()->translatedFormat('F Y')}})</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>

@if(auth()->check() && in_array(auth()->user()->role, ['Admin', 'cvsr']))
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Insert Logbook</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <form id="formEdit" action="{{ route('logbook.insert') }}" method="POST">
        @csrf
        <input type="hidden" name="id" id="insert_id">

        <div class="modal-body">

          <div class="form-group">
            <label>Komitmen</label>
            <select id="edit_komitmen" class="form-control" name="komitmen">
              <option value="New Leads">New Leads</option>
              <option value="100%">100%</option>
              <option value="50%">50%</option>
              <option value="<50%">&lt;50%</option>
            </select>
          </div>

          <div class="form-group">
            <label>Plan Min Topup</label>
            <input type="number" id="edit_plan" class="form-control" name="plan_min_topup">
          </div>

          <div class="form-group">
            <label>Status</label>
            <select id="edit_status" class="form-control" name="status">
              <option value="Initial">Initial</option>
              <option value="Prospect">Prospect</option>
              <option value="Register">Register</option>
              {{-- <option value="Topup">Topup</option> --}}
              <option value="Repeat">Repeat</option>
              <option value="No Response">No Response</option>
              {{-- <option value="Reject">Reject</option> --}}
            </select>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>

      </form>

    </div>
  </div>
</div>
@endif
@endsection


@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).on('click', '.btn-add-logbook', function() {
    // alert($(this).data('id'));
    let id = $(this).data('id');

    // set id ke form modal
    $('#insert_id').val(id);

    // buka modal
    $('#modalEdit').modal('show');
});

$(function () {

    $('.select2').select2({ width: '100%' });

    let table = $('#leadsMasterTable').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ajax: {
            url: "{{ route('leads-master.data') }}",
            data: function (d) {
                d.canvasser = $('#filter_canvasser').val();
                // d.company   = $('#filter_company').val();
                // d.email     = $('#filter_email').val();
                // d.source    = $('#filter_source').val();
                d.start_date = $('#start_date').val();
                d.end_date   = $('#end_date').val();
                d.regional = $('#filter_regional').val();
            }
        },
        columns: [
            // { data: 'status', orderable: false, searchable: false },
            { data: 'user_name', searchable: true },
            { data: 'regional', searchable: true },
            { data: 'company_name', searchable: true },
            { data: 'email', searchable: true },
            { data: 'mobile_phone', searchable: true },
            { data: 'data_type', searchable: false },
            { data: 'created_at', searchable: false },
            { data: 'total_settlement_klien', searchable: false },
            { data: 'aksi', orderable: false, searchable: false }
        ]
    });

    // $('#filter_canvasser, #filter_source').on('change', function () {
    //     table.ajax.reload();
    // });

    // $('#filter_company, #filter_email').on('keyup', function () {
    //     table.ajax.reload();
    // });
      $('#btnRefresh').on('click', function () {
        table.ajax.reload(null, false);
    });
});
$('#start_date').on('change', function () {
    let startDate = $(this).val();

    if (startDate) {
        // End date tidak boleh sebelum start date
        $('#end_date').attr('min', startDate);

        // Kalau end date < start date → reset
        if ($('#end_date').val() && $('#end_date').val() < startDate) {
            $('#end_date').val('');
        }
    }
});

$('#end_date').on('change', function () {
    let endDate = $(this).val();

    if (endDate) {
        // Start date tidak boleh setelah end date
        $('#start_date').attr('max', endDate);

        // Kalau start date > end date → reset
        if ($('#start_date').val() && $('#start_date').val() > endDate) {
            $('#start_date').val('');
        }
    }
});

$('#btnExport').on('click', function () {

    let params = {
        start_date: $('#start_date').val(),
        end_date: $('#end_date').val(),
        canvasser: $('#filter_canvasser').val(),
        // company: $('#filter_company').val(),
        // email: $('#filter_email').val(),
        // source: $('#filter_source').val(),
    };

    let query = $.param(params);

    window.location = "{{ route('leads-master.export') }}?" + query;
});
$('#btnExport').on('click', function () {
    let params = {
        start_date: $('#start_date').val(),
        end_date: $('#end_date').val()
    };

    window.location =
        "{{ route('leads-master.export') }}?" + $.param(params);
});
</script>
@endsection
