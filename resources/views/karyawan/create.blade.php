@extends('layouts.app')

@section('title', 'Tambah Data Karyawan')
@section('header-title', 'Tambah Karyawan Baru')

@section('content')
    @push('head-scripts')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    @endpush
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('karyawan.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nik" class="form-label">NIK</label>
                        <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik"
                            name="nik" value="{{ old('nik') }}" required>
                        @error('nik')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama"
                            name="nama" value="{{ old('nama') }}" required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender"
                            required>
                            <option value="" disabled selected>Pilih Gender</option>
                            <option value="Laki-laki" {{ old('gender') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki
                            </option>
                            <option value="Perempuan" {{ old('gender') == 'Perempuan' ? 'selected' : '' }}>Perempuan
                            </option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="formasi_select" class="form-label">Pilih Formasi</label>
                        <select class="form-select @error('kode_jabatan') is-invalid @enderror" id="formasi_select"
                            name="formasi_select" required onchange="updateFormasiFields()">
                            <option value="" disabled selected>Pilih Formasi</option>
                            @foreach ($formasiList as $formasi)
                                <option value="{{ $formasi->id }}" data-kode_jabatan="{{ $formasi->kode_jabatan }}"
                                    data-lokasi="{{ $formasi->lokasi }}" data-unit="{{ $formasi->unit }}"
                                    data-jabatan="{{ $formasi->jabatan }}"
                                    data-kkj="{{ $formasi->kelompok_kelas_jabatan }}" data-grade="{{ $formasi->grade }}"
                                    {{ old('formasi_select') == $formasi->id ? 'selected' : '' }}>
                                    [{{ $formasi->kode_jabatan }}] {{ $formasi->jabatan }} - {{ $formasi->unit }}
                                    ({{ $formasi->lokasi }})
                                </option>
                            @endforeach
                        </select>
                        @error('kode_jabatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <input type="hidden" id="kode_jabatan" name="kode_jabatan" value="{{ old('kode_jabatan') }}">
                    <input type="hidden" id="lokasi" name="lokasi" value="{{ old('lokasi') }}">
                    <input type="hidden" id="unit" name="unit" value="{{ old('unit') }}">
                    <input type="hidden" id="jabatan" name="jabatan" value="{{ old('jabatan') }}">
                    <input type="hidden" id="kelompok_kelas_jabatan" name="kelompok_kelas_jabatan"
                        value="{{ old('kelompok_kelas_jabatan') }}">
                    <input type="hidden" id="grade" name="grade" value="{{ old('grade') }}">
                    <div class="col-md-6 mb-3">
                        <label for="status_kepegawaian" class="form-label">Status Kepegawaian</label>
                        <input type="text" class="form-control @error('status_kepegawaian') is-invalid @enderror"
                            id="status_kepegawaian" name="status_kepegawaian" value="{{ old('status_kepegawaian') }}"
                            required>
                        @error('status_kepegawaian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                            id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>
                        @error('tanggal_lahir')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pendidikan_terakhir" class="form-label">Pendidikan Terakhir</label>
                        <input type="text" class="form-control @error('pendidikan_terakhir') is-invalid @enderror"
                            id="pendidikan_terakhir" name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir') }}"
                            required>
                        @error('pendidikan_terakhir')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tmt" class="form-label">TMT (Terhitung Mulai Tanggal)</label>
                        <input type="date" class="form-control @error('tmt') is-invalid @enderror" id="tmt"
                            name="tmt" value="{{ old('tmt') }}" required>
                        @error('tmt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    @push('body-scripts')
                        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
                        <script>
                            function updateFormasiFields() {
                                var select = document.getElementById('formasi_select');
                                var selected = select.options[select.selectedIndex];
                                document.getElementById('kode_jabatan').value = selected.getAttribute('data-kode_jabatan') || '';
                                document.getElementById('lokasi').value = selected.getAttribute('data-lokasi') || '';
                                document.getElementById('unit').value = selected.getAttribute('data-unit') || '';
                                document.getElementById('jabatan').value = selected.getAttribute('data-jabatan') || '';
                                document.getElementById('kelompok_kelas_jabatan').value = selected.getAttribute('data-kkj') || '';
                                document.getElementById('grade').value = selected.getAttribute('data-grade') || '';
                            }
                            document.addEventListener('DOMContentLoaded', function() {
                                const formasiSelect = document.getElementById('formasi_select');
                                if (formasiSelect) {
                                    new Choices(formasiSelect, {
                                        searchEnabled: true,
                                        itemSelectText: '',
                                        shouldSort: false
                                    });
                                }
                            });
                        </script>
                    @endpush
                    <a href="{{ route('karyawan.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
