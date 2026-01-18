@extends('master')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
@endsection

@section('content')
<style>.fc-event {
    border-radius: 6px !important;
    padding: 2px 6px !important;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.4;
}

.fc-daygrid-event {
    margin-bottom: 3px;
}

.fc-event-title {
    white-space: normal !important;
}

.fc-daygrid-day-number {
    font-weight: 600;
    color: #6c757d;
}

/* Highlight today */
.fc-day-today {
    background: #fff3cd !important;
}

/* Header */
.fc-toolbar-title {
    font-weight: 600;
    font-size: 18px;
}
</style>
<div class="d-flex justify-content-between mb-2">
    <h5>Calendar Booking</h5>
    <div>
        <button class="btn btn-success" id="btnDownload">
            <i class="fa fa-download"></i> Download Calendar
        </button>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalBooking">
            + Tambah Booking
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-10">
        <div id="calendar"></div>
    </div>
    <div class="col-md-1"></div>
</div>
<br>
<hr>
<br>
<div class="row mb-5">
    <div class="col-md-3">
        <h5>Detail Booking</h5>
        <div id="detail">
            <input type="hidden" id="event-id">
            <p><b>Nama:</b> <span id="d-nama">-</span></p>
            <p><b>Lokasi:</b> <span id="d-lokasi">-</span></p>
            <p><b>Tanggal:</b> <span id="d-tanggal">-</span></p>
            <p><b>Waktu:</b> <span id="d-waktu">-</span></p>
            <p><b>Keterangan:</b> <span id="d-keterangan">-</span></p>

            <button class="btn btn-danger btn-sm mt-2" id="btnDelete">
                <i class="fa fa-trash"></i> Hapus Booking
            </button>
        </div>
    </div>

</div>

<div class="modal fade" id="modalBooking" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="formBooking">
            @csrf
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Tambah Booking</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Nama</label>
                        <select name="nama" class="form-control" id="namaSelect" required>
                            <option value="">-- Pilih Nama --</option>

                            <option value="Robert" data-color="#e74c3c">Robert</option>
                            <option value="Luky" data-color="#3498db">Luky</option>
                            <option value="Cici" data-color="#9b59b6">Cici</option>
                            <option value="Novrand" data-color="#1abc9c">Novrand</option>
                            <option value="Angga Satria Gusti" data-color="#f39c12">Angga Satria Gusti</option>
                            <option value="Abdul Halim" data-color="#2ecc71">Abdul Halim</option>
                            <option value="Raden Agie Satria Akbar" data-color="#e84393">Raden Agie Satria Akbar</option>
                            <option value="Sony Widjaya" data-color="#34495e">Sony Widjaya</option>
                            <option value="Deni Setiawan" data-color="#16a085">Deni Setiawan</option>
                            <option value="Muhammad Arief Syahbana" data-color="#d35400">Muhammad Arief Syahbana</option>
                            <option value="Naqsyabandi" data-color="#7f8c8d">Naqsyabandi</option>
                            <option value="Ikrar Dharmawan" data-color="#2980b9">Ikrar Dharmawan</option>
                        </select>

                        <!-- hidden untuk kirim warna -->
                        <input type="hidden" name="color" id="colorInput">
                    </div>


                    <div class="form-group">
                        <label>Lokasi</label>
                        <input type="text" name="lokasi" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label>Mulai</label>
                            <input type="time" name="start" class="form-control" required>
                        </div>
                        <div class="col">
                            <label>Selesai</label>
                            <input type="time" name="end" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group mt-2">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>


<script>
let calendar;
let selectedEventId = null;

document.addEventListener('DOMContentLoaded', function () {

    calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: "{{ url('/calendar/events') }}",

        eventClick: function(info) {
            let e = info.event.extendedProps;

            selectedEventId = info.event.id;
            $('#event-id').val(selectedEventId);

            $('#d-nama').text(e.nama || '-');
            $('#d-lokasi').text(e.lokasi || '-');
            $('#d-tanggal').text(e.tanggal || '-');
            $('#d-waktu').text(e.waktu || '-');
            $('#d-keterangan').text(e.keterangan || '-');
        }
    });

    calendar.render();
});

/* ======================
   TAMBAH BOOKING
====================== */
$('#formBooking').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        url: "{{ url('/calendar/store') }}",
        method: "POST",
        data: $(this).serialize(),
        success: function () {
            $('#modalBooking').modal('hide');
            calendar.refetchEvents();
            $('#formBooking')[0].reset();
        }
    });
});

/* ======================
   DELETE BOOKING
====================== */
$('#btnDelete').on('click', function () {

    if (!selectedEventId) {
        alert('Pilih event terlebih dahulu');
        return;
    }

    if (!confirm('Yakin ingin menghapus booking ini?')) return;

    $.ajax({
        url: "{{ url('/calendar/delete') }}/" + selectedEventId,
        method: "DELETE",
        data: {
            _token: "{{ csrf_token() }}"
        },
        success: function () {
            calendar.refetchEvents();
            selectedEventId = null;

            $('#detail span').text('-');
            alert('Booking berhasil dihapus');
        }
    });
});

/* ======================
   DOWNLOAD CALENDAR
====================== */
$('#btnDownload').on('click', function () {
    window.location.href = "{{ url('/calendar/download') }}";
});
$('#namaSelect').on('change', function () {
    // let color = $(this).find(':selected').data('color') || '#3788d8';
    $('#colorInput').val(color);

    // optional: ubah warna select biar kelihatan
    // $(this).css({
    //     'background-color': color,
    //     'color': '#fff'
    // });
});

</script>
@endsection


