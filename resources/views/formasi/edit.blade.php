@extends('layouts.app')

@section('title', 'Edit Data Formasi')
@section('header-title', 'Edit Data Formasi')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('formasi.update', $formasi->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kuota" class="form-label">Kuota</label>
                        <input type="number" min="1" class="form-control @error('kuota') is-invalid @enderror"
                            id="kuota" name="kuota" value="{{ old('kuota', $formasi->kuota) }}" required>
                        @error('kuota')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="kode_jabatan" class="form-label">Kode Jabatan</label>
                        <input type="text" class="form-control @error('kode_jabatan') is-invalid @enderror"
                            id="kode_jabatan" name="kode_jabatan" value="{{ old('kode_jabatan', $formasi->kode_jabatan) }}"
                            required>
                        @error('kode_jabatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control @error('lokasi') is-invalid @enderror" id="lokasi"
                            name="lokasi" value="{{ old('lokasi', $formasi->lokasi) }}" required>
                        @error('lokasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="unit" class="form-label">Unit</label>
                        <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit"
                            name="unit" value="{{ old('unit', $formasi->unit) }}" required>
                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="jabatan" class="form-label">Jabatan</label>
                        <input type="text" class="form-control @error('jabatan') is-invalid @enderror" id="jabatan"
                            name="jabatan" value="{{ old('jabatan', $formasi->jabatan) }}" required>
                        @error('jabatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="kelompok_kelas_jabatan" class="form-label">Kelompok Kelas Jabatan</label>
                        <input type="text" class="form-control @error('kelompok_kelas_jabatan') is-invalid @enderror"
                            id="kelompok_kelas_jabatan" name="kelompok_kelas_jabatan"
                            value="{{ old('kelompok_kelas_jabatan', $formasi->kelompok_kelas_jabatan) }}" required>
                        @error('kelompok_kelas_jabatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="grade" class="form-label">Grade</label>
                        <input type="text" class="form-control @error('grade') is-invalid @enderror" id="grade"
                            name="grade" value="{{ old('grade', $formasi->grade) }}" required>
                        @error('grade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('formasi.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection
