@extends('master')
@section('title') Input Data Panen Poin @endsection

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .card {
        animation: fadeInUp 0.6s ease-out;
    }

    .form-label {
        font-weight: 600;
        color: #5a5c69;
    }

    .btn-submit {
        background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
        border: none;
        padding: 10px 30px;
    }

    .btn-submit:hover {
        background: linear-gradient(180deg, #224abe 10%, #4e73df 100%);
    }
</style>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-gradient-info text-white">
                <h5 class="mb-0"><i class="fas fa-user-plus mr-2"></i>Form Input Data Pelanggan Panen Poin</h5>
            </div>

            <form id="formInputPanen" action="{{ route('panenpoin.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <!-- Hidden flag untuk mendeteksi success di JavaScript -->
                    @if(session('success'))
                        <input type="hidden" id="successMessage" value="{{ session('success') }}">
                    @endif

                    @if(session('error'))
                        <input type="hidden" id="errorMessage" value="{{ session('error') }}">
                    @endif
                        <label for="nama_pelanggan" class="form-label">
                            Nama Pelanggan <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control @error('nama_pelanggan') is-invalid @enderror"
                            id="nama_pelanggan"
                            name="nama_pelanggan"
                            value="{{ old('nama_pelanggan') }}"
                            placeholder="Masukkan nama pelanggan"
                            required>
                        @error('nama_pelanggan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="akun_myads_pelanggan" class="form-label">
                            Akun MyAds Pelanggan (Email) <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                            class="form-control @error('akun_myads_pelanggan') is-invalid @enderror"
                            id="akun_myads_pelanggan"
                            name="akun_myads_pelanggan"
                            value="{{ old('akun_myads_pelanggan') }}"
                            placeholder="contoh@email.com"
                            required>
                        @error('akun_myads_pelanggan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="nomor_hp_pelanggan" class="form-label">
                            Nomor HP Pelanggan <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control @error('nomor_hp_pelanggan') is-invalid @enderror"
                            id="nomor_hp_pelanggan"
                            name="nomor_hp_pelanggan"
                            value="{{ old('nomor_hp_pelanggan') }}"
                            placeholder="08xxxxxxxxxx"
                            required>
                        @error('nomor_hp_pelanggan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <small>Pastikan data yang diinput sudah benar sebelum menyimpan.</small>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-submit" id="btnSubmit">
                        <i class="fas fa-save mr-2"></i>Simpan Data
                    </button>
                    <a href="{{ route('panenpoin.report') }}" class="btn btn-secondary">
                        <i class="fas fa-chart-bar mr-2"></i>Lihat Report
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
    $(document).ready(function() {
        // Handle form submit dengan AJAX
        $('#formInputPanen').on('submit', function(e) {
            e.preventDefault();
            
            // Validasi form
            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return false;
            }
            
            // Show loading
            var btnSubmit = $('#btnSubmit');
            var originalText = btnSubmit.html();
            btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Sedang memproses...');
            
            // Kirim via AJAX
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    // Success alert
                    Swal.fire({
                        title: 'Sukses! 🎉',
                        html: 'Data pelanggan berhasil disimpan dan akun telah dibuat!<br><br><small style="color: #666;">Halaman akan di-refresh setelah Anda klik OK...</small>',
                        icon: 'success',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        confirmButtonColor: '#4caf50',
                        confirmButtonText: 'OK',
                    }).then(function() {
                        // Refresh halaman
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    // Error alert
                    var errorMsg = 'Gagal menyimpan data!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        title: 'Terjadi Kesalahan! ⚠️',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Tutup',
                    });
                    
                    // Reset button
                    btnSubmit.prop('disabled', false).html(originalText);
                }
            });
        });

        // Validate phone number
        $('#nomor_hp_pelanggan').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
</script>
@endsection