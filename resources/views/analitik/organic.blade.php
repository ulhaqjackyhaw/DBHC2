@extends('layouts.app')

@section('title', 'Analitik Karyawan Organik')
@section('header-title', 'Analitik Karyawan Organik')

@push('head-scripts')
    {{-- Dependensi & CSS --}}
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3.0.1/dist/chartjs-plugin-annotation.min.js">
    </script>

    <style>
        :root {
            --body-bg: #f8fafc;
            --card-bg: #ffffff;
            --text-color-dark: #1e293b;
            --text-color-light: #64748b;
            --border-color: #e2e8f0;
            --primary-color: #4f46e5;
        }

        body {
            background-color: var(--body-bg);
            font-family: 'Inter', sans-serif;
        }

        .card {
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-color-dark);
        }

        .chart-container {
            position: relative;
        }

        .nav-tabs {
            border-bottom: 1px solid var(--border-color);
        }

        .nav-tabs .nav-link {
            border-width: 0;
            border-bottom: 2px solid transparent;
            color: var(--text-color-light);
            font-weight: 600;
            padding: 0.75rem 1.25rem;
        }

        .nav-tabs .nav-link.active {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
            background: none;
        }

        .tab-content {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-top: 0;
            padding: 2rem;
            border-radius: 0 0 1rem 1rem;
        }


        .table-matrix td {
            text-align: center;
            font-weight: 600;
            padding: 0.75rem;
        }


        .table-matrix .grade-label,
        .cell-name {
            text-align: left;
        }

        .cell-role {
            font-size: .85rem;
            color: #64748b;
            text-align: left;
        }

        .table thead th {
            background-color: #f1f5f9;
            font-weight: 600;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            padding: 0.4rem 0.75rem;
        }

        .quadrant-legend {
            list-style: none;
            padding-left: 0;
        }

        .quadrant-legend li {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .quadrant-legend .icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 0.5rem;
            margin-right: 1rem;
            font-size: 1.2rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card mb-4">
            <div class="d-flex flex-column align-items-center justify-content-center p-3">
                <div style="background-color: #e0e7ff; color: #4338ca; width: 64px; height: 64px; border-radius: 50%; display: grid; place-items: center; font-size: 2rem; flex-shrink: 0;"
                    class="mb-2"><i class="fa-solid fa-users"></i></div>
                <div class="text-center">
                    <div style="font-size: 2.5rem; font-weight: 700;">{{ number_format($totalOrganic ?? 0) }}</div>
                    <div style="font-size: 1rem; color: var(--text-color-light);">Total Karyawan Organik</div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs" id="analyticsTab" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab"
                    data-bs-target="#dashboard" type="button">📊 Anilis 1</button></li>
            {{-- <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#matriks"
                    type="button">🧬 Analisis 1</button></li> --}}
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab"
                    data-bs-target="#lanjutan" type="button">📖 Analisis 2</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#unit"
                    type="button">🏢 Analisis 3</button></li>
        </ul>

        <div class="tab-content" id="analyticsTabContent">
            <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                {{-- Konten Dashboard tidak berubah --}}
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Piramida Jabatan per Lokasi</h5>
                                <div class="d-flex flex-row flex-wrap align-items-start">
                                    <div class="chart-container flex-grow-1" style="height:400px; min-width:0;">
                                        <canvas id="kkjLocationChart"></canvas>
                                        <div class="small text-muted mt-2 text-center w-100">
                                            <i class="fa fa-info-circle text-info"></i>
                                            Klik pada Kotak warna BOD- di bawah chart untuk menyembunyikan/menampilkan jenis
                                            jabatan tertentu.
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <br>
                                <br>
                                @if (isset($kkjLocationLabels) && isset($kkjLocationLabels[0]) && isset($kkjLocationDatasets))
                                    <div class="mt-4" style="max-width:100%; overflow-x:auto;">
                                        <div class="fw-bold mb-2">Total & Rincian per Lokasi</div>
                                        <table class="table table-sm table-bordered mb-0 align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-nowrap">Lokasi</th>
                                                    @foreach ($kkjLocationDatasets as $ds)
                                                        <th class="text-end">{{ $ds['label'] }}</th>
                                                    @endforeach
                                                    <th class="text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($kkjLocationLabels as $i => $lokasi)
                                                    <tr>
                                                        <td class="text-nowrap">{{ $lokasi }}</td>
                                                        @php $rowTotal = 0; @endphp
                                                        @foreach ($kkjLocationDatasets as $ds)
                                                            <td class="text-end">{{ number_format($ds['data'][$i] ?? 0) }}
                                                            </td>
                                                            @php $rowTotal += $ds['data'][$i] ?? 0; @endphp
                                                        @endforeach
                                                        <td class="text-end fw-bold">{{ number_format($rowTotal) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    {{-- Card Jumlah Karyawan Organik per Lokasi --}}
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title">👥 Jumlah Karyawan Organik per Lokasi</h5>
                            <p class="text-muted small mb-3">
                                <strong>Distribusi jumlah karyawan organik</strong> berdasarkan lokasi geografis.<br>
                                <i class="fa fa-info-circle text-info"></i> <strong>Data:</strong> Total karyawan organik •
                                Persentase dari total • Status kepegawaian organik
                            </p>

                            <div class="row">
                                @if (isset($locationSummary) && $locationSummary->count() > 0)
                                    @foreach ($locationSummary as $location)
                                        <div class="col-md-6 col-lg-4 col-xl-3 mb-3">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body text-center">
                                                    <div class="display-6 text-primary mb-2">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    </div>
                                                    <h6 class="card-title fw-bold text-truncate"
                                                        title="{{ $location['location'] }}">
                                                        {{ $location['location'] }}
                                                    </h6>
                                                    <div class="display-4 fw-bold text-dark mb-2">
                                                        {{ number_format($location['total_employees']) }}
                                                    </div>
                                                    <p class="text-muted mb-3">karyawan organik</p>

                                                    {{-- Progress bar untuk proporsi relatif --}}
                                                    @if (isset($totalOrganic) && $totalOrganic > 0)
                                                        <div class="mt-3">
                                                            @php
                                                                $percentage =
                                                                    ($location['total_employees'] / $totalOrganic) *
                                                                    100;
                                                            @endphp
                                                            <div class="small text-muted mb-1">
                                                                {{ number_format($percentage, 1) }}% dari total organik
                                                            </div>
                                                            <div class="progress" style="height: 8px;">
                                                                <div class="progress-bar bg-primary"
                                                                    style="width: {{ $percentage }}%">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="text-center py-5">
                                            <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">Data lokasi tidak tersedia</h6>
                                            <p class="text-muted small">Belum ada data karyawan dengan informasi lokasi
                                                yang lengkap</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Summary Statistics --}}
                            @if (isset($locationSummary) && $locationSummary->count() > 0)
                                <div class="mt-4 p-3 bg-light rounded-3">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="small text-muted">Total Lokasi</div>
                                            <div class="h5 fw-bold text-primary mb-0">{{ $locationSummary->count() }}
                                                lokasi
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small text-muted">Lokasi Terbesar</div>
                                            <div class="h6 fw-bold text-success mb-0">
                                                @php
                                                    $largestLocation = $locationSummary
                                                        ->sortByDesc('total_employees')
                                                        ->first();
                                                @endphp
                                                {{ $largestLocation['location'] }}<br>
                                                <small
                                                    class="text-muted">({{ number_format($largestLocation['total_employees']) }}
                                                    karyawan)</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small text-muted">Rata-rata per Lokasi</div>
                                            <div class="h5 fw-bold text-info mb-0">
                                                {{ number_format($locationSummary->avg('total_employees'), 0) }} karyawan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title">Daftar Nama Karyawan berdasarkan Kelompokan Kelas Jabatan</h6>
                                <div style="max-height: 400px; overflow-y: auto;">
                                    <div class="table-responsive">
                                        @php
                                            $bodGroups = $bodGroups ?? [];
                                            $maxRows = max(
                                                count($bodGroups['BOD-1'] ?? []),
                                                count($bodGroups['BOD-2'] ?? []),
                                                count($bodGroups['BOD-3'] ?? []),
                                                count($bodGroups['BOD-4'] ?? []),
                                            );
                                        @endphp
                                        <table class="table table-sm table-fixed align-top">
                                            <thead>
                                                <tr>
                                                    <th style="width:25%">BOD-1</th>
                                                    <th style="width:25%">BOD-2</th>
                                                    <th style="width:25%">BOD-3</th>
                                                    <th style="width:25%">BOD-4</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @for ($i = 0; $i < $maxRows; $i++)
                                                    <tr>
                                                        <td>
                                                            @if (isset($bodGroups['BOD-1'][$i]))
                                                                <div class="cell-name">
                                                                    {{ $bodGroups['BOD-1'][$i]['nama'] }}
                                                                </div>
                                                                <div class="cell-role">
                                                                    {{ $bodGroups['BOD-1'][$i]['jabatan'] }}</div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (isset($bodGroups['BOD-2'][$i]))
                                                                <div class="cell-name">
                                                                    {{ $bodGroups['BOD-2'][$i]['nama'] }}
                                                                </div>
                                                                <div class="cell-role">
                                                                    {{ $bodGroups['BOD-2'][$i]['jabatan'] }}</div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (isset($bodGroups['BOD-3'][$i]))
                                                                <div class="cell-name">
                                                                    {{ $bodGroups['BOD-3'][$i]['nama'] }}
                                                                </div>
                                                                <div class="cell-role">
                                                                    {{ $bodGroups['BOD-3'][$i]['jabatan'] }}</div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (isset($bodGroups['BOD-4'][$i]))
                                                                <div class="cell-name">
                                                                    {{ $bodGroups['BOD-4'][$i]['nama'] }}
                                                                </div>
                                                                <div class="cell-role">
                                                                    {{ $bodGroups['BOD-4'][$i]['jabatan'] }}</div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endfor
                                                @if ($maxRows === 0)
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted p-4">Data BOD
                                                            tidak
                                                            tersedia.</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Distribusi Gender per Lokasi</h5>
                                <div class="chart-container" style="height:250px;"><canvas
                                        id="genderLocationChart"></canvas></div>
                                <div class="small text-muted mt-2 text-center w-100">
                                    <i class="fa fa-info-circle text-info"></i>
                                    Klik pada Kotak warna di bawah chart untuk menyembunyikan/menampilkan jenis
                                    tertentu.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Distribusi Generasi per Lokasi</h5>
                                <div class="chart-container" style="height:250px;"><canvas id="ageChart"></canvas></div>
                                <div class="small text-muted mt-2 text-center w-100">
                                    <i class="fa fa-info-circle text-info"></i>
                                    Klik pada Kotak warna di bawah chart untuk menyembunyikan/menampilkan jenis
                                    tertentu.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Distribusi Masa Kerja per Lokasi</h5>
                                <div class="chart-container" style="height:250px;"><canvas
                                        id="tenureLocationChart"></canvas></div>
                                <div class="small text-muted mt-2 text-center w-100">
                                    <i class="fa fa-info-circle text-info"></i>
                                    Klik pada Kotak warna di bawah chart untuk menyembunyikan/menampilkan jenis
                                    tertentu.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>





            <div class="tab-pane fade" id="lanjutan" role="tabpanel">
                {{-- Konten Lanjutan tidak berubah --}}
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Distribusi Pendidikan Regional 1 (Organik)</h5>
                                <div class="chart-container" style="height:350px;"><canvas id="educationChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">📍 Distribusi Pendidikan per Lokasi</h5>
                                <p class="text-muted small mb-3">
                                    <strong>Analisis sebaran tingkat pendidikan</strong> di setiap lokasi geografis.<br>
                                    <i class="fa fa-info-circle text-info"></i> <strong>Data:</strong> Karyawan organik •
                                    Semua unit kerja • Breakdown per tingkat pendidikan
                                </p>

                                {{-- Tabel Distribusi Pendidikan per Lokasi --}}
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" rowspan="2" style="vertical-align: middle;">
                                                    Lokasi</th>
                                                <th class="text-center" colspan="9">Tingkat Pendidikan</th>
                                                <th class="text-center" rowspan="2" style="vertical-align: middle;">
                                                    Total</th>
                                                <th class="text-center" rowspan="2" style="vertical-align: middle;">
                                                    Skor</th>
                                                <th class="text-center" rowspan="2" style="vertical-align: middle;">
                                                    Pendidikan Dominan</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center">S3</th>
                                                <th class="text-center">S2</th>
                                                <th class="text-center">S1</th>
                                                <th class="text-center">D4</th>
                                                <th class="text-center">D3</th>
                                                <th class="text-center">D2</th>
                                                <th class="text-center">D1</th>
                                                <th class="text-center">SMA/SMK</th>
                                                <th class="text-center">Lainnya</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($locationMatrix) && !empty($locationMatrix))
                                                @php
                                                    // Urutkan berdasarkan skor pendidikan (tertinggi ke terendah)
                                                    $sortedLocations = collect($locationMatrix)->sortByDesc(function (
                                                        $data,
                                                    ) {
                                                        return $data['education_score'] ?? 0;
                                                    });
                                                @endphp
                                                @foreach ($sortedLocations as $location => $data)
                                                    <tr class="{{ $loop->iteration <= 3 ? 'table-warning' : '' }}">
                                                        <td class="fw-semibold">
                                                            @if ($loop->iteration <= 3)
                                                                <span
                                                                    class="badge bg-warning text-dark me-2">{{ $loop->iteration }}</span>
                                                            @endif
                                                            {{ $location }}
                                                        </td>
                                                        <td class="text-center">{{ $data['S3'] ?? 0 }}</td>
                                                        <td class="text-center">{{ $data['S2'] ?? 0 }}</td>
                                                        <td class="text-center">{{ $data['S1'] ?? 0 }}</td>
                                                        <td class="text-center">{{ $data['D4'] ?? 0 }}</td>
                                                        <td class="text-center">{{ $data['D3'] ?? 0 }}</td>
                                                        <td class="text-center">{{ $data['D2'] ?? 0 }}</td>
                                                        <td class="text-center">{{ $data['D1'] ?? 0 }}</td>
                                                        <td class="text-center">{{ $data['SMA/SMK'] ?? 0 }}</td>
                                                        <td class="text-center">{{ $data['Lainnya'] ?? 0 }}</td>
                                                        <td class="text-center fw-bold">{{ $data['total'] ?? 0 }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary">
                                                                {{ number_format($data['education_score'] ?? 0, 1) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-success">
                                                                {{ $data['highest_education'] ?? '-' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                {{-- Row Total/Summary --}}
                                                <tr class="table-secondary fw-bold">
                                                    <td>TOTAL REGIONAL 1</td>
                                                    @php
                                                        $totals = [
                                                            'S3' => 0,
                                                            'S2' => 0,
                                                            'S1' => 0,
                                                            'D4' => 0,
                                                            'D3' => 0,
                                                            'D2' => 0,
                                                            'D1' => 0,
                                                            'SMA/SMK' => 0,
                                                            'Lainnya' => 0,
                                                            'total' => 0,
                                                        ];
                                                        foreach ($locationMatrix as $data) {
                                                            foreach ($totals as $key => $value) {
                                                                $totals[$key] += $data[$key] ?? 0;
                                                            }
                                                        }
                                                    @endphp
                                                    <td class="text-center">{{ $totals['S3'] }}</td>
                                                    <td class="text-center">{{ $totals['S2'] }}</td>
                                                    <td class="text-center">{{ $totals['S1'] }}</td>
                                                    <td class="text-center">{{ $totals['D4'] }}</td>
                                                    <td class="text-center">{{ $totals['D3'] }}</td>
                                                    <td class="text-center">{{ $totals['D2'] }}</td>
                                                    <td class="text-center">{{ $totals['D1'] }}</td>
                                                    <td class="text-center">{{ $totals['SMA/SMK'] }}</td>
                                                    <td class="text-center">{{ $totals['Lainnya'] }}</td>
                                                    <td class="text-center">{{ $totals['total'] }}</td>
                                                    <td class="text-center" colspan="2">
                                                        <span class="badge bg-dark">
                                                            {{ $totals['total'] > 0 ? number_format((($totals['S1'] + $totals['S2'] + $totals['S3']) / $totals['total']) * 100, 1) : 0 }}%
                                                            Sarjana+
                                                        </span>
                                                    </td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td colspan="13" class="text-center text-muted py-4">
                                                        Data distribusi pendidikan tidak tersedia
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3 p-3 bg-light rounded-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="small text-muted">
                                                <strong>📊 Metodologi Penilaian Skor:</strong><br>
                                                • S3/Doktor: 9 poin | S2/Master: 8 poin | S1/Sarjana: 7 poin<br>
                                                • D4: 6 poin | D3: 5 poin | D2: 4 poin | D1: 3 poin<br>
                                                • SMA/SMK: 2 poin | Lainnya: 1 poin<br>
                                                • <strong>Skor Lokasi</strong> = AVG(poin semua karyawan dalam lokasi)
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-muted">
                                                <strong>🎯 Interpretasi Data:</strong><br>
                                                • <strong>Tinggi (7-9):</strong> Dominasi S1/S2/S3<br>
                                                • <strong>Sedang (4-6):</strong> Mix diploma & sarjana<br>
                                                • <strong>Rendah (1-3):</strong> Dominasi SMA/SMK<br>
                                                • <span class="badge bg-warning text-dark">Top 3</span> = Lokasi dengan
                                                skor tertinggi
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="unit" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Tabel Komparasi per Unit (Pivot Unit x Lokasi)</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Unit</th>
                                        @foreach ($allLocations as $lokasi)
                                            <th>{{ $lokasi }}</th>
                                        @endforeach
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sortedUnits = collect($allUnits)
                                            ->sortByDesc(function ($unit) use ($unitLocationPivot) {
                                                return $unitLocationPivot[$unit]['total'] ?? 0;
                                            })
                                            ->toArray();
                                    @endphp
                                    @foreach ($sortedUnits as $unit)
                                        <tr>
                                            <td><strong>{{ $unit }}</strong></td>
                                            @foreach ($allLocations as $lokasi)
                                                <td class="text-end">
                                                    {{ $unitLocationPivot[$unit][$lokasi] ?? 0 }}
                                                </td>
                                            @endforeach
                                            <td class="text-end fw-bold">{{ $unitLocationPivot[$unit]['total'] ?? 0 }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="small text-muted mt-2">* Setiap sel = jumlah karyawan pada unit & lokasi tsb</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('body-scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#employeesTable').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                }
            });

            if (window.ChartDataLabels) Chart.register(ChartDataLabels);
            if (window.ChartAnnotation) Chart.register(ChartAnnotation);

            const palette = {
                primary: '#4f46e5',
                secondary: '#db2777',
                amber: '#f97316',
                green: '#16a34a',
                violet: '#8b5cf6'
            };
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.plugins.legend.position = 'bottom';
            Chart.defaults.plugins.datalabels = {
                color: '#FFFFFF',
                font: {
                    weight: 'bold'
                },
                formatter: (v) => (v > 0 ? (v % 1 !== 0 ? v.toFixed(1) : v) : '')
            };

            let chartInstances = {};
            const createChart = (ctxId, type, data, options = {}) => {
                if (chartInstances[ctxId]) chartInstances[ctxId].destroy();
                const ctx = document.getElementById(ctxId);
                if (ctx) chartInstances[ctxId] = new Chart(ctx.getContext('2d'), {
                    type,
                    data,
                    options
                });
            };

            const chartInitializers = {
                '#dashboard': () => {
                    // Piramida Jabatan per Lokasi (horizontal grouped bar)
                    @if (isset($kkjLocationLabels) && isset($kkjLocationDatasets))
                        createChart('kkjLocationChart', 'bar', {
                            labels: @json($kkjLocationLabels),
                            datasets: @json($kkjLocationDatasets)
                        }, {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                datalabels: {
                                    color: '#fff',
                                    font: {
                                        weight: 'bold'
                                    },
                                    formatter: (value) => (value > 0 ? value : '')
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    beginAtZero: true
                                },
                                y: {
                                    stacked: true
                                }
                            }
                        });
                    @endif

                    // Gender per lokasi (horizontal grouped/stacked bar)
                    createChart('genderLocationChart', 'bar', {
                        labels: @json($genderLocationLabels),
                        datasets: @json($genderLocationDatasets)
                    }, {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            datalabels: {
                                color: '#fff',
                                font: {
                                    weight: 'bold'
                                },
                                formatter: (value) => (value > 0 ? value : '')
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                beginAtZero: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    });

                    // Generasi per lokasi (horizontal grouped/stacked bar)
                    createChart('ageChart', 'bar', {
                        labels: @json($ageLocationLabels),
                        datasets: @json($ageLocationDatasets)
                    }, {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            datalabels: {
                                color: '#fff',
                                font: {
                                    weight: 'bold'
                                },
                                formatter: (value) => (value > 0 ? value : '')
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                beginAtZero: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    });

                    // Masa kerja per lokasi (horizontal grouped/stacked bar)
                    createChart('tenureLocationChart', 'bar', {
                        labels: @json($tenureLocationLabels),
                        datasets: @json($tenureLocationDatasets)
                    }, {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            datalabels: {
                                color: '#fff',
                                font: {
                                    weight: 'bold'
                                },
                                formatter: (value) => (value > 0 ? value : '')
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                beginAtZero: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    });
                },


                '#lanjutan': () => {
                    createChart('educationChart', 'bar', {
                        labels: @json($educationCounts->pluck('pendidikan_terakhir')),
                        datasets: [{
                            data: @json($educationCounts->pluck('total')),
                            backgroundColor: palette.violet
                        }]
                    }, {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    });

                    // Chart distribusi pendidikan per lokasi
                    @if (isset($locationMatrix) && !empty($locationMatrix))
                        const locationData = @json($locationMatrix);
                        const educationLevels = @json($educationLevels) || ['SMP', 'SMA/SMK', 'D1',
                            'D2', 'D3', 'D4', 'S1', 'S2', 'S3'
                        ];
                        const locations = Object.keys(locationData);

                        const educationColors = {
                            'S3': '#ef9400',
                            'S2': '#ef00e5',
                            'S1': '#6900ef',
                            'D4': '#0018ef',
                            'D3': '#00efea',
                            'D2': '#00ef0a',
                            'D1': '#efef00',
                            'SMA/SMK': '#ef0000',
                            'SMP': '#0ABAB5'
                        };

                        const datasets = educationLevels.map(level => ({
                            label: level,
                            data: locations.map(location => locationData[location][level] || 0),
                            backgroundColor: educationColors[level] || palette.primary,
                            borderRadius: 2
                        }));

                        createChart('locationEducationChart', 'line', {
                            labels: locations,
                            datasets: datasets
                        }, {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                },
                                datalabels: {
                                    display: false // Nonaktifkan label untuk chart yang kompleks
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Jumlah Karyawan'
                                    }
                                }
                            }
                        });
                    @endif
                }
            };

            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', (e) => {
                const initializer = chartInitializers[e.target.getAttribute('data-bs-target')];
                if (initializer) initializer();
            });

            // Inisialisasi tab pertama
            chartInitializers['#dashboard']();
        });
    </script>
@endpush
