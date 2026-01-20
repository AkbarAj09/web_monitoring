@extends('master')
@section('title') Logbook Daily @endsection

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
            {{-- <select id="filter_regional" class="form-control" style="max-width: 200px">
                <option value="">All Regional</option>
                @foreach($regionals as $regional)
                    <option value="{{ $regional }}">{{ $regional }}</option>
                @endforeach
            </select> --}}

            {{-- Date --}}
            <input type="date"
           id="start_date"
           class="form-control"
           style="max-width: 160px"
           value="{{ now()->toDateString() }}">

            <input type="date"
                id="end_date"
                class="form-control"
                style="max-width: 160px"
                value="{{ now()->toDateString() }}">
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
        <h4 class="font-weight-bold">Logbook Daily</h4>
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
                    <th>Status</th>
                    <th>Realisasi Topup</th>
                    {{-- <th>Action</th> --}}

                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
{{-- <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Edit Logbook</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <form id="formEdit" action="{{ route('logbook.updateDaily') }}" method="POST">
        @csrf
        <input type="hidden" name="id" id="edit_id">

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
              <option value="Topup">Topup</option>
              <option value="Repeat">Repeat</option>
              <option value="No Response">No Response</option>
              <option value="Reject">Reject</option>
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
</div> --}}

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

// $(document).on('click', '.btn-edit', function () {
//     $('#edit_id').val($(this).data('id'));
//     $('#edit_komitmen').val($(this).data('komitmen'));
//     $('#edit_plan').val($(this).data('plan'));
//     $('#edit_status').val($(this).data('status'));

//     $('#modalEdit').modal('show');
// });
// $('#formEdit').on('submit', function (e) {
//     e.preventDefault();

//     $.ajax({
//         url: $(this).attr('action'),   // <-- ambil dari form
//         type: 'POST',
//         data: $(this).serialize(),     // <-- otomatis ambil name=""
//         success: function () {
//             $('#modalEdit').modal('hide');
//             $('#leadsMasterTable').DataTable().ajax.reload(null, false);
//         },
//         error: function (xhr) {
//             alert('Update gagal');
//             console.log(xhr.responseText);
//         }
//     });
// });


$(function () {

    let table = $('#leadsMasterTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('logbook-daily.data') }}",
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
            { data: 'status' },
            { data: 'total_settlement_klien' },
            // { data: 'action', orderable: false, searchable: false },
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
