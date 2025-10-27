@extends('layouts.app')

@section('title', 'Analitik Karyawan Outsourcing')
@section('header-title', 'Analitik Karyawan Outsourcing')

@push('head-scripts')
    {{-- Dependensi & CSS --}}
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

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
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        {{-- Total Karyawan --}}
        <div class="card mb-4">
            <div class="d-flex flex-column align-items-center justify-content-center p-3">
                <div style="background-color: #e0e7ff; color: #4338ca; width: 64px; height: 64px; border-radius: 50%; display: grid; place-items: center; font-size: 2rem; flex-shrink: 0;"
                    class="mb-2"><i class="fa-solid fa-users"></i></div>
                <div class="text-center">
                    <div style="font-size: 2.5rem; font-weight: 700;">{{ number_format($totalOutsourcing ?? 0) }}</div>
                    <div style="font-size: 1rem; color: var(--text-color-light);">Total Karyawan Outsourcing</div>
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Distribusi Gender per Lokasi</h5>
                        <div class="chart-container" style="height:350px;"><canvas id="genderChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Distribusi Generasi per Lokasi</h5>
                        <div class="chart-container" style="height:350px;"><canvas id="ageChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Distribusi Instansi per Lokasi</h5>
                        <div class="chart-container" style="height:350px;"><canvas id="instansiChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Distribusi per Unit</h5>
                        <div class="chart-container" style="height:350px;"><canvas id="unitChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Detail --}}
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tabel Komparasi Outsourcing (Pivot Unit x Instansi x Lokasi)</h5>
                <div class="table-responsive" style="max-width:100vw; overflow-x:auto;">
                    @php
                        // Kumpulkan semua lokasi unik
                        $allLocations = collect($employees)->pluck('lokasi')->unique()->sort()->values();
                        // Kumpulkan semua kombinasi unit & instansi
                        $unitInstansi = collect($employees)
                            ->map(function ($e) {
                                return [$e->unit, $e->asal_instansi];
                            })
                            ->unique()
                            ->sortBy(function ($arr) {
                                return $arr[0] . '|' . $arr[1];
                            })
                            ->values();
                        // Bangun pivot: [unit][instansi][lokasi] = count
                        $pivot = [];
                        foreach ($employees as $e) {
                            $pivot[$e->unit][$e->asal_instansi][$e->lokasi] =
                                ($pivot[$e->unit][$e->asal_instansi][$e->lokasi] ?? 0) + 1;
                            $pivot[$e->unit][$e->asal_instansi]['total'] =
                                ($pivot[$e->unit][$e->asal_instansi]['total'] ?? 0) + 1;
                        }
                    @endphp
                    <table class="table table-bordered table-hover align-middle" id="pivotOutsourcingTable">
                        <thead class="table-light">
                            <tr>
                                <th>Unit</th>
                                <th>Asal Instansi</th>
                                @foreach ($allLocations as $lokasi)
                                    <th>{{ $lokasi }}</th>
                                @endforeach
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($unitInstansi as $pair)
                                @php [$unit, $instansi] = $pair; @endphp
                                <tr>
                                    <td><strong>{{ $unit }}</strong></td>
                                    <td>{{ $instansi }}</td>
                                    @foreach ($allLocations as $lokasi)
                                        <td class="text-end">{{ $pivot[$unit][$instansi][$lokasi] ?? 0 }}</td>
                                    @endforeach
                                    <td class="text-end fw-bold">{{ $pivot[$unit][$instansi]['total'] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="small text-muted mt-2">* Setiap sel = jumlah karyawan outsourcing pada kombinasi unit, instansi,
                    dan lokasi tsb</div>
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
            // DataTable untuk tabel pivot outsourcing
            $('#pivotOutsourcingTable').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                "lengthMenu": [10, 100, 1000],
                "pageLength": 10,
                "scrollX": true,
                "ordering": true
            });

            if (window.ChartDataLabels) Chart.register(ChartDataLabels);

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
                formatter: (value) => (value > 0 ? value : '')
            };

            const createChart = (ctxId, type, data, options = {}) => {
                const ctx = document.getElementById(ctxId);
                if (ctx) new Chart(ctx.getContext('2d'), {
                    type,
                    data,
                    options
                });
            };

            // Gender per Lokasi (Grouped Bar)
            createChart('genderChart', 'bar', {
                labels: @json($genderLocationLabels),
                datasets: @json($genderLocationDatasets)
            }, {
                responsive: true,
                maintainAspectRatio: false,
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
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                }
            });

            // Generasi per Lokasi
            createChart('ageChart', 'bar', {
                labels: @json($ageLocationLabels),
                datasets: @json($ageLocationDatasets)
            }, {
                responsive: true,
                maintainAspectRatio: false,
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
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                }
            });

            // Instansi per Lokasi
            createChart('instansiChart', 'bar', {
                labels: @json($instansiLocationLabels),
                datasets: @json($instansiLocationDatasets)
            }, {
                responsive: true,
                maintainAspectRatio: false,
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
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                }
            });

            // Unit
            createChart('unitChart', 'bar', {
                labels: @json($unitCounts->pluck('unit')),
                datasets: [{
                    label: 'Jumlah Karyawan',
                    data: @json($unitCounts->pluck('total')),
                    backgroundColor: palette.violet
                }]
            }, {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            });
        });
    </script>
@endpush
