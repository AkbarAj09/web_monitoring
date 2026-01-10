@extends('master')

@section('title', 'Eksisting Akun')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Eksisting Akun</h3>
    </div>

    <div class="card-body">
        <form action="{{ route('leads-master.store-existing') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- USER NAME --}}
            <div class="form-group">
                <label>User Canvasser</label>
                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
            </div>


            {{-- NAMA PERUSAHAAN / INSTANSI --}}
            <div class="form-group">
                <label for="company_name">Nama Perusahaan / Instansi</label>
                <input type="text" id="company_name" name="company_name" class="form-control" 
                    placeholder="Masukkan nama perusahaan atau instansi" value="{{ old('company_name') }}">
                @error('company_name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- NO HP --}}
            <div class="form-group">
                <label for="mobile_phone">No HP Pelanggan</label>
                <input type="text" id="mobile_phone" name="mobile_phone" class="form-control" 
                    placeholder="Masukkan nomor HP pelanggan" value="{{ old('mobile_phone') }}">
                @error('mobile_phone')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            
            {{-- EMAIL --}}
            <div class="form-group">
                <label for="email">Email Pelanggan</label>
                <input type="email" id="email" name="email" class="form-control" 
                    placeholder="Masukkan email pelanggan" value="{{ old('email') }}">
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            

            {{-- NAMA PELANGGAN --}}
            <div class="form-group">
                <label for="nama">Nama Pelanggan</label>
                <input type="text" id="nama" name="nama" class="form-control" 
                    placeholder="Masukkan nama pelanggan" value="{{ old('nama') }}">
                @error('nama')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- SECTOR --}}
            <div class="form-group">
                <label for="sector_id">Sector</label>
                <select name="sector_id" id="sector_id" class="form-control select2">
                    {{-- <option value="">-- Pilih Sector --</option> --}}
                    @foreach ($sectors as $sector)
                        <option value="{{ $sector->id }}" {{ old('sector_id') == $sector->id ? 'selected' : '' }}>
                            {{ $sector->name }}
                        </option>
                    @endforeach
                </select>
                @error('sector_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            {{-- Akun Myads --}}
            <div class="form-group">
                <label for="myads_account">Akun MyAds</label>
                <input type="text" id="myads_account" name="myads_account" class="form-control" 
                    placeholder="Masukkan Akun MyAds" value="{{ old('myads_account') }}" required>
                    {{-- <small class="text-danger">*) Diisi jika sudah register akun MyAds</small> --}}
                @error('myads_account')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            {{-- STATUS DEAL --}}
            {{-- <div class="form-group" style="display:none">
                <label>Status Deal</label>
                <div class="d-flex gap-3">
                    <label>
                        <input type="radio" name="status" value="No" {{ old('status') == 'No' ? 'checked' : '' }}> No
                    </label>
                    <label>
                        <input type="radio" name="status" value="Ok" {{ old('status', 'Ok') == 'Ok' ? 'checked' : '' }}> Ok
                    </label>
                </div>
                @error('status')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div> --}}

            {{-- REMARKS --}}
            <div class="form-group">
                <label for="remarks">Remarks</label>
                <textarea id="remarks" name="remarks" class="form-control" rows="3" 
                    placeholder="Tambahkan catatan jika perlu">{{ old('remarks') }}</textarea>
                @error('remarks')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group d-flex gap-2">
                <a href="{{ route('leads-master.index') }}" class="btn btn-secondary flex-grow-1 m-1">Kembali</a>
                <button type="submit" class="btn btn-primary flex-grow-1 m-1">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "-- Pilih --",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
