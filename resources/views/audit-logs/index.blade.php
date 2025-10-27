@extends('layouts.app')

@section('title', 'Audit Log - Riwayat Perubahan Data')

@section('header-title', 'Audit Log - Riwayat Perubahan Data')

@push('head-scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .filter-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .audit-card {
            border-left: 4px solid #6c757d;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }

        .audit-card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .audit-card.action-created {
            border-left-color: #28a745;
        }

        .audit-card.action-updated {
            border-left-color: #ffc107;
        }

        .audit-card.action-deleted {
            border-left-color: #dc3545;
        }

        .audit-card.action-restored {
            border-left-color: #17a2b8;
        }

        .change-item {
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }

        .old-value {
            color: #dc3545;
            text-decoration: line-through;
        }

        .new-value {
            color: #28a745;
            font-weight: 600;
        }

        .menu-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .menu-badge.badge-karyawan {
            background: #e0f2fe;
            color: #0284c7;
        }

        .menu-badge.badge-formasi {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .menu-badge.badge-realisasi {
            background: #dcfce7;
            color: #15803d;
        }

        .menu-badge.badge-data-pgs {
            background: #fef3c7;
            color: #b45309;
        }

        .menu-badge.badge-user {
            background: #fee2e2;
            color: #b91c1c;
        }
    </style>

    <script>
        // Auto-sync menu filter dengan model type
        document.addEventListener('DOMContentLoaded', function() {
            const menuFilter = document.getElementById('menuFilter');
            const modelTypeSelect = document.querySelector('select[name="model_type"]');

            if (menuFilter && modelTypeSelect) {
                menuFilter.addEventListener('change', function() {
                    const selectedMenu = this.value;

                    // Clear model_type ketika menu filter dipilih
                    if (selectedMenu) {
                        modelTypeSelect.value = '';
                        modelTypeSelect.disabled = true;
                    } else {
                        modelTypeSelect.disabled = false;
                    }
                });

                // Set initial state
                if (menuFilter.value) {
                    modelTypeSelect.disabled = true;
                }
            }
        });
    </script>
@endpush

@section('content')
    <div class="container-fluid">

        {{-- Quick Filter Buttons --}}
        <div class="mb-3">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('audit-logs.index') }}"
                    class="btn btn-sm {{ !request('menu_filter') ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="bi bi-grid-3x3-gap"></i> Semua Menu
                </a>
                <a href="{{ route('audit-logs.index', ['menu_filter' => 'karyawan']) }}"
                    class="btn btn-sm {{ request('menu_filter') == 'karyawan' ? 'btn-info' : 'btn-outline-info' }}">
                    <i class="bi bi-people-fill"></i> Data Karyawan
                </a>
                <a href="{{ route('audit-logs.index', ['menu_filter' => 'formasi']) }}"
                    class="btn btn-sm {{ request('menu_filter') == 'formasi' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-building-fill"></i> Formasi
                </a>
                <a href="{{ route('audit-logs.index', ['menu_filter' => 'realisasi']) }}"
                    class="btn btn-sm {{ request('menu_filter') == 'realisasi' ? 'btn-success' : 'btn-outline-success' }}">
                    <i class="bi bi-table"></i> Realisasi
                </a>
                <a href="{{ route('audit-logs.index', ['menu_filter' => 'data-pgs']) }}"
                    class="btn btn-sm {{ request('menu_filter') == 'data-pgs' ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="bi bi-person-badge-fill"></i> Data PGS
                </a>
                <a href="{{ route('audit-logs.index', ['menu_filter' => 'user']) }}"
                    class="btn btn-sm {{ request('menu_filter') == 'user' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="bi bi-person-plus-fill"></i> User Management
                </a>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-search"></i> Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama, email, identifier..."
                        value="{{ request('search') }}">
                </div>


                <div class="col-md-2">
                    <label class="form-label"><i class="bi bi-lightning-fill"></i> Aksi</label>
                    <select name="action" class="form-select">
                        <option value="">Semua Aksi</option>
                        <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Menambah Data
                        </option>
                        <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Mengubah Data
                        </option>
                        <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Menghapus Data
                        </option>
                        <option value="restored" {{ request('action') == 'restored' ? 'selected' : '' }}>Memulihkan Data
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label"><i class="bi bi-person-fill"></i> User</label>
                    <select name="user_id" class="form-select">
                        <option value="">Semua User</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label"><i class="bi bi-calendar-event"></i> Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label"><i class="bi bi-calendar-event"></i> Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel-fill"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Reset Filter
                    </a>
                </div>
            </form>
        </div>

        {{-- Statistics --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        {{-- Active Filters Display --}}
                        @if (request('menu_filter') ||
                                request('model_type') ||
                                request('action') ||
                                request('user_id') ||
                                request('search') ||
                                request('start_date') ||
                                request('end_date'))
                            <div class="mb-3 pb-3 border-bottom">
                                <strong class="text-muted"><i class="bi bi-funnel"></i> Filter Aktif:</strong>
                                <div class="mt-2">
                                    @if (request('menu_filter'))
                                        <span class="menu-badge badge-{{ request('menu_filter') }}">
                                            <i class="bi bi-menu-button-wide"></i>
                                            Menu:
                                            @switch(request('menu_filter'))
                                                @case('karyawan')
                                                    Data Karyawan
                                                @break

                                                @case('formasi')
                                                    Formasi
                                                @break

                                                @case('realisasi')
                                                    Realisasi
                                                @break

                                                @case('data-pgs')
                                                    Data PGS
                                                @break

                                                @case('user')
                                                    User Management
                                                @break
                                            @endswitch
                                        </span>
                                    @endif

                                    @if (request('action'))
                                        <span class="badge bg-info">
                                            <i class="bi bi-lightning-fill"></i>
                                            Aksi:
                                            @switch(request('action'))
                                                @case('created')
                                                    Menambah Data
                                                @break

                                                @case('updated')
                                                    Mengubah Data
                                                @break

                                                @case('deleted')
                                                    Menghapus Data
                                                @break

                                                @case('restored')
                                                    Memulihkan Data
                                                @break
                                            @endswitch
                                        </span>
                                    @endif

                                    @if (request('user_id'))
                                        @php
                                            $selectedUser = $users->firstWhere('id', request('user_id'));
                                        @endphp
                                        @if ($selectedUser)
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-person-fill"></i>
                                                User: {{ $selectedUser->name }}
                                            </span>
                                        @endif
                                    @endif

                                    @if (request('search'))
                                        <span class="badge bg-dark">
                                            <i class="bi bi-search"></i>
                                            Pencarian: "{{ request('search') }}"
                                        </span>
                                    @endif

                                    @if (request('start_date') || request('end_date'))
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-calendar-event"></i>
                                            Periode:
                                            {{ request('start_date') ? date('d/m/Y', strtotime(request('start_date'))) : '...' }}
                                            s/d
                                            {{ request('end_date') ? date('d/m/Y', strtotime(request('end_date'))) : '...' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <h6 class="card-subtitle mb-2 text-muted">
                            <i class="bi bi-graph-up"></i> Total: <strong>{{ $auditLogs->total() }}</strong> aktivitas
                            ditemukan
                        </h6>
                    </div>
                </div>
            </div>
        </div>

        {{-- Audit Logs List --}}
        @forelse($auditLogs as $log)
            <div class="card audit-card action-{{ $log->action }}">
                <div class="card-body">
                    <div class="row align-items-start">
                        <div class="col-md-8">
                            <h5 class="card-title mb-2">
                                <span class="badge bg-{{ $log->action_badge_color }}">{{ $log->action_name }}</span>
                                <strong>{{ $log->model_name }}</strong>
                                @if ($log->model_identifier)
                                    <span class="text-muted">- {{ $log->model_identifier }}</span>
                                @endif
                            </h5>

                            @if ($log->action === 'updated' && !empty($log->changes))
                                <div class="mt-2">
                                    <strong class="text-muted"><i class="bi bi-pencil-square"></i> Perubahan:</strong>
                                    <div class="ms-3 mt-2">
                                        @foreach ($log->getChangesSummary() as $change)
                                            <div class="change-item">
                                                <strong>{{ $change['field'] }}:</strong>
                                                @if (isset($change['is_sensitive']) && $change['is_sensitive'])
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-shield-lock"></i> Diubah (data sensitif)
                                                    </span>
                                                @else
                                                    <span class="old-value">{{ $change['old'] }}</span>
                                                    <i class="bi bi-arrow-right"></i>
                                                    <span class="new-value">{{ $change['new'] }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4 text-end">
                            <div class="mb-2">
                                <i class="bi bi-person-circle"></i>
                                <strong>{{ $log->user_name ?? 'System' }}</strong>
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-envelope"></i> {{ $log->user_email ?? '-' }}
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-clock"></i> {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-globe"></i> {{ $log->ip_address ?? '-' }}
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">Tidak ada data audit log yang ditemukan</p>
                </div>
            </div>
        @endforelse

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $auditLogs->links() }}
        </div>
    </div>
@endsection
