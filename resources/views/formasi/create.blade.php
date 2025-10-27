@extends('layouts.app')

@section('title', 'Tambah Data Formasi')
@section('header-title', 'Tambah Formasi Baru')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('formasi.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kode_jabatan" class="form-label">Kode Jabatan</label>
                        <input type="text" class="form-control @error('kode_jabatan') is-invalid @enderror"
                            id="kode_jabatan" name="kode_jabatan" value="{{ old('kode_jabatan') }}" required>
                        @error('kode_jabatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control @error('lokasi') is-invalid @enderror" id="lokasi"
                            name="lokasi" value="{{ old('lokasi') }}" required>
                        @error('lokasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="unit" class="form-label">Unit</label>
                        <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit"
                            name="unit" value="{{ old('unit') }}" required>
                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="jabatan" class="form-label">Jabatan</label>
                        <input type="text" class="form-control @error('jabatan') is-invalid @enderror" id="jabatan"
                            name="jabatan" value="{{ old('jabatan') }}" required>
                        @error('jabatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="kelompok_kelas_jabatan" class="form-label">Kelompok Kelas Jabatan</label>
                        <input type="text" class="form-control @error('kelompok_kelas_jabatan') is-invalid @enderror"
                            id="kelompok_kelas_jabatan" name="kelompok_kelas_jabatan"
                            value="{{ old('kelompok_kelas_jabatan') }}" required>
                        @error('kelompok_kelas_jabatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="col-md-6 mb-3">
                            <label for="kuota" class="form-label">Kuota</label>
                            <input type="number" min="1" class="form-control @error('kuota') is-invalid @enderror"
                                id="kuota" name="kuota" value="{{ old('kuota', 1) }}" required>
                            @error('kuota')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <label for="grade" class="form-label">Grade</label>
                        <input type="text" class="form-control @error('grade') is-invalid @enderror" id="grade"
                            name="grade" value="{{ old('grade') }}" required>
                        @error('grade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('formasi.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
