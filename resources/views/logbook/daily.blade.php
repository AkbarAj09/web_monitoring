@extends('master')
@section('title') Logbook Daily @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet"/>
<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" rel="stylesheet"/>
<style>
    .card-title { font-weight: bold; }
    .select2-container .select2-selection--single {
        height: 35px !important;
        padding: 6px 10px;
    }

    /* Filter Card Styling */
    .filter-card {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .filter-card h5 {
        background-color: #495057;
        color: white;
        padding: 12px 15px;
        margin: -20px -20px 15px -20px;
        border-radius: 7px 7px 0 0;
        font-weight: 600;
        font-size: 15px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 6px;
        color: #333;
    }

    .filter-group small {
        font-size: 11px;
        color: #6c757d;
        margin-top: 2px;
        font-weight: normal;
    }

    .filter-group input,
    .filter-group select {
        width: 100%;
    }

    .loading-spinner {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        text-align: center;
    }

    .loading-spinner.active {
        display: block;
    }

    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9998;
    }

    .loading-overlay.active {
        display: block;
    }

    .spinner-border-sm {
        width: 2rem;
        height: 2rem;
    }
</style>
@endsection

@section('content')

<!-- Loading Overlay -->
<div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 9999;">
    <div id="loading-spinner" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; z-index: 10000;">
        <i class="fas fa-spinner fa-spin" style="font-size: 48px; color: white; margin-bottom: 10px; display: block;"></i>
        <p style="color: white; font-size: 16px;">Loading data...</p>
    </div>
</div>

<!-- Filter Card -->
<div class="filter-card">
    <h5><i class="fas fa-filter"></i> FILTER DATA LOGBOOK DAILY</h5>
    
    <div class="filter-row">
        @if(Auth::user()->role === 'Admin')
        <div class="filter-group">
            <label for="filter_canvasser">Canvasser</label>
            <select id="filter_canvasser" class="form-control select2">
                <option value="">Semua Canvasser</option>
                @foreach($canvassers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <small>Pilih canvasser untuk melihat logbook spesifik</small>
        </div>
        @endif

        <div class="filter-group">
            <label for="start_date">Tanggal Mulai</label>
            <input type="date" id="start_date" class="form-control" value="{{ now()->toDateString() }}">
            <small>Pilih tanggal awal periode</small>
        </div>

        <div class="filter-group">
            <label for="end_date">Tanggal Akhir</label>
            <input type="date" id="end_date" class="form-control" value="{{ now()->toDateString() }}">
            <small>Pilih tanggal akhir periode</small>
        </div>

        <div class="filter-group">
            <button id="btnExport" class="btn btn-success w-100" style="height: 38px;">
                <i class="fa fa-file-excel"></i> Export Excel
            </button>
            <small style="color: #28a745; margin-top: 6px;">Download data sesuai filter</small>
        </div>
    </div>
</div>


<div class="row mb-3" id="summaryCards">

    <div class="col-md-2">
        <div class="card text-white bg-primary">
            <div class="card-body p-2">
                <small>New Leads</small>
                <div class="d-flex justify-content-between">
                    <span>Plan</span>
                    <strong id="sum_new_leads">0</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Realisasi</span>
                    <strong id="real_new_leads">0</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card text-white bg-success">
            <div class="card-body p-2">
                <small>Komitmen 100%</small>
                <div class="d-flex justify-content-between">
                    <span>Plan</span>
                    <strong id="sum_100">0</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Realisasi</span>
                    <strong id="real_100">0</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card text-white bg-info">
            <div class="card-body p-2">
                <small>Komitmen 50%</small>
                <div class="d-flex justify-content-between">
                    <span>Plan</span>
                    <strong id="sum_50">0</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Realisasi</span>
                    <strong id="real_50">0</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card text-white bg-warning">
            <div class="card-body p-2">
                <small>Komitmen &lt;50%</small>
                <div class="d-flex justify-content-between">
                    <span>Plan</span>
                    <strong id="sum_less_50">0</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Realisasi</span>
                    <strong id="real_less_50">0</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-dark text-white">
            <div class="card-body p-2">
                <small>Total</small>
                <div class="d-flex justify-content-between">
                    <span>Plan</span>
                    <strong id="sum_total">0</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Realisasi</span>
                    <strong id="real_total">0</strong>
                </div>
            </div>
        </div>
    </div>

</div>




{{-- TABLE --}}
<div class="card">
    <div class="card-header bg-danger text-white">
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
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function rupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka ?? 0);
}

function showLoading() {
    $('#loading-overlay').show();
}

function hideLoading() {
    $('#loading-overlay').hide();
}

function loadSummary() {
    $.ajax({
        url: "{{ route('logbook-daily.summary') }}",
        data: {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            canvasser: $('#filter_canvasser').val()
        },
        success: function (res) {
            $('#sum_new_leads').text(rupiah(res.new_leads));
            $('#real_new_leads').text(rupiah(res.real_new_leads));

            $('#sum_100').text(rupiah(res.full));
            $('#real_100').text(rupiah(res.real_full));

            $('#sum_50').text(rupiah(res.half));
            $('#real_50').text(rupiah(res.real_half));

            $('#sum_less_50').text(rupiah(res.less_half));
            $('#real_less_50').text(rupiah(res.real_less_half));

            $('#sum_total').text(rupiah(res.total));
            $('#real_total').text(rupiah(res.real_total));
        }
    });
}

loadSummary();

$(function () {
    $('.select2').select2({ width: '100%' });

    let table = $('#leadsMasterTable').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        responsive: true,
        ajax: {
            url: "{{ route('logbook-daily.data') }}",
            data: function (d) {
                d.canvasser   = $('#filter_canvasser').val();
                d.start_date = $('#start_date').val();
                d.end_date   = $('#end_date').val();
            },
            beforeSend: function() {
                showLoading();
            },
            complete: function() {
                hideLoading();
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
            { data: 'realisasi_topup' },
        ]
    });

    // Auto-reload table ketika filter berubah
    $('#filter_canvasser').on('change', function () {
        table.ajax.reload();
        loadSummary();
    });

    $('#start_date').on('change', function () {
        let startDate = $(this).val();

        if (startDate) {
            $('#end_date').attr('min', startDate);

            if ($('#end_date').val() && $('#end_date').val() < startDate) {
                $('#end_date').val('');
            }
        }

        table.ajax.reload();
        loadSummary();
    });

    $('#end_date').on('change', function () {
        let endDate = $(this).val();

        if (endDate) {
            $('#start_date').attr('max', endDate);

            if ($('#start_date').val() && $('#start_date').val() > endDate) {
                $('#start_date').val('');
            }
        }

        table.ajax.reload();
        loadSummary();
    });

    // Export dengan filter yang sedang diterapkan
    $('#btnExport').on('click', function () {
        let params = {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            canvasser: $('#filter_canvasser').val()
        };

        window.location = "{{ route('leads-master.export') }}?" + $.param(params);
    });

});
</script>
@endsection
