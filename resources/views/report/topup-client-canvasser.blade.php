@extends('master')
@section('title') Topup Client Canvasser @endsection

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

    /* Optional: limit max width and enable horizontal scroll */
    .dataTables_wrapper {
        width: 100%;
        overflow-x: auto;
    }

    table.dataTable th,
    table.dataTable td {
        white-space: nowrap; /* prevent wrapping */
    }
</style>
@endsection

@section('content')
<div class="container">
    <h2>Topup & Client Canvasser</h2>

    <table id="topupCanvasserTable" border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: center;">
        <thead>
            <tr style="background-color: #007bff; color: white;">
                <th rowspan="2">Date</th>

                @foreach ($canvassers as $canvasser)
                    <th colspan="2">{{ $canvasser }}</th>
                @endforeach

                <th colspan="2">Grand total</th>
            </tr>
            <tr style="background-color: #007bff; color: white;">
                @foreach ($canvassers as $_)
                    <th>total_settlement_klien</th>
                    <th>email</th>
                @endforeach
                <th>total_settlement_klien</th>
                <th>email</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($data as $row)
                <tr @if($loop->iteration % 2 == 0) style="background-color: #f0f0f0;" @endif>
                    <td style="text-align: left; padding-left: 10px;">{{ $row['tanggal'] }}</td>

                    @foreach ($canvassers as $c)
                        <td style="text-align: right; padding-right: 10px;">
                            {{ is_numeric($row[$c.'_amount']) ? number_format($row[$c.'_amount'], 0, '.', ',') : $row[$c.'_amount'] }}
                        </td>
                        <td>{{ $row[$c.'_email'] }}</td>
                    @endforeach

                    <td style="text-align: right; font-weight: bold; padding-right: 10px;">
                        {{ is_numeric($row['total_amount']) ? number_format($row['total_amount'], 0, '.', ',') : $row['total_amount'] }}
                    </td>
                    <td style="font-weight: bold;">{{ $row['total_email'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(function () {
    $('.select2').select2({ width: '100%' });

    $('#topupCanvasserTable').DataTable({
        scrollX: true,
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        // Optional: fixed header
        // fixedHeader: true,
        language: {
            emptyTable: "No data available"
        }
    });
});
</script>
@endsection
