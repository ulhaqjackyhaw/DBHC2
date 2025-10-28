@extends('layouts.app')

@section('title', 'Data Formasi')
@section('header-title', 'Data Formasi')

@push('head-styles')
    <style>
        /* Container Size */
        .formasi-container {
            width: 935px !important;
            height: 978px !important;
            max-height: 978px !important;
            overflow-y: auto;
            overflow-x: auto;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .formasi-container>* {
            max-width: 100%;
        }

        /* Compact table styling */
        .table-compact {
            font-size: 0.875rem;
        }

        .table-compact th,
        .table-compact td {
            padding: 0.5rem 0.75rem;
            white-space: nowrap;
            vertical-align: middle;
        }

        /* Set max width untuk kolom tertentu */
        .table-compact td:nth-child(3) {
            /* Unit Deputy EGM */
            max-width: 150px;
            white-space: normal;
            word-wrap: break-word;
        }

        .table-compact td:nth-child(4) {
            /* Unit Assistant Deputy */
            max-width: 150px;
            white-space: normal;
            word-wrap: break-word;
        }

        .table-compact td:nth-child(5) {
            /* Unit Division Head */
            max-width: 150px;
            white-space: normal;
            word-wrap: break-word;
        }

        .table-compact td:nth-child(6) {
            /* Unit Department Head */
            max-width: 150px;
            white-space: normal;
            word-wrap: break-word;
        }

        .table-compact td:nth-child(7) {
            /* Lokasi Kerja */
            max-width: 120px;
            white-space: normal;
            word-wrap: break-word;
        }

        .table-compact td:nth-child(8) {
            /* Unit Kerja */
            max-width: 150px;
            white-space: normal;
            word-wrap: break-word;
        }

        .table-compact td:nth-child(9) {
            /* Jabatan */
            max-width: 200px;
            white-space: normal;
            word-wrap: break-word;
        }

        /* Kolom aksi tetap kecil */
        .table-compact td:last-child {
            width: 1%;
            white-space: nowrap;
        }

        .table-responsive {
            transform-origin: top left;
            transition: transform 0.3s ease;
            border-radius: 0.5rem;
            overflow-x: auto;
        }
    </style>
@endpush

@section('content')
    <div class="formasi-container">
        {{-- Notifikasi Sukses dan Error --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @can('admin')
            {{-- Bagian Upload Massal --}}
            <div class="bg-white p-4 sm:p-5 rounded-xl shadow-sm mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h2 class="text-lg font-semibold text-slate-800 mb-0">Upload Data Massal</h2>
                    <a href="{{ route('formasi.template.download') }}"
                        class="btn btn-outline-success d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-arrow-down-fill"></i>
                        <span>Download Template</span>
                    </a>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <form action="{{ route('formasi.import.add') }}" method="POST" enctype="multipart/form-data"
                            class="d-flex gap-3">
                            @csrf
                            <input type="file" name="file" class="form-control" required>
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 text-nowrap"><i
                                    class="bi bi-cloud-arrow-up-fill"></i> Tambah</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form action="{{ route('formasi.import.replace') }}" method="POST" enctype="multipart/form-data"
                            class="d-flex gap-3">
                            @csrf
                            <input type="file" name="file" class="form-control" required>
                            <button type="submit" class="btn btn-danger d-flex align-items-center gap-2 text-nowrap"><i
                                    class="bi bi-arrow-repeat"></i> Ganti Semua</button>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Tabel Data --}}
        <div class="bg-white rounded-xl shadow-sm p-3" x-data="formasiTable({{ $formasi->toJson() ?? '[]' }})">
            <div class="p-3">
                {{-- Header Tabel --}}
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        @can('admin')
                            <a href="{{ route('formasi.create') }}"
                                class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                <i class="bi bi-plus-circle-fill"></i>
                                <span class="text-nowrap">Tambah Data</span>
                            </a>
                        @endcan
                        <a href="{{ route('formasi.export') }}"
                            class="btn btn-success btn-sm d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                            <span class="text-nowrap">Download Data Excel</span>
                        </a>
                    </div>
                    {{-- Sisi Kanan: Filter dan Search --}}
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <label for="itemsPerPage"
                                class="form-label text-nowrap mb-0 text-slate-600 small">Tampilkan</label>
                            <select id="itemsPerPage" class="form-select form-select-sm" style="width: auto;"
                                x-model.number="itemsPerPage">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="1000">1000</option>
                            </select>
                            <span class="text-slate-600 text-nowrap small">data</span>
                        </div>
                        <div style="width: 200px;">
                            <input type="text" class="form-control form-control-sm" placeholder="Cari formasi..."
                                x-model.debounce.300ms="searchTerm">
                        </div>
                    </div>
                </div>

                {{-- Tabel --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-compact">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="text-slate-500 font-semibold text-nowrap small">No</th>
                                <th @click="sortBy('kode_jabatan')"
                                    class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap small">
                                    Kode
                                    Jabatan <i :class="sortIcon('kode_jabatan')"></i></th>
                                <th @click="sortBy('unit_deputy_egm')"
                                    class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap small">
                                    Unit
                                    Deputy
                                    EGM <i :class="sortIcon('unit_deputy_egm')"></i></th>
                                <th @click="sortBy('unit_assistant_deputy')"
                                    class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap small">
                                    Unit
                                    Assistant Deputy <i :class="sortIcon('unit_assistant_deputy')"></i></th>
                                <th @click="sortBy('unit_division_head')"
                                    class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap small">
                                    Unit
                                    Division Head <i :class="sortIcon('unit_division_head')"></i></th>
                                <th @click="sortBy('unit_department_head')"
                                    class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap small">
                                    Unit
                                    Department Head <i :class="sortIcon('unit_department_head')"></i></th>
                                <th @click="sortBy('lokasi_kerja')"
                                    class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap small">
                                    Lokasi
                                    Kerja <i :class="sortIcon('lokasi_kerja')"></i></th>
                                <th @click="sortBy('unit_kerja')"
                                    class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap small">
                                    Unit
                                    Kerja
                                    <i :class="sortIcon('unit_kerja')"></i>
                                </th>
                                <th @click="sortBy('jabatan')"
                                    class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap small">
                                    Jabatan
                                    <i :class="sortIcon('jabatan')"></i>
                                </th>
                                <th @click="sortBy('kelompok_kelas_jabatan')"
                                    class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap small">
                                    KKJ <i :class="sortIcon('kelompok_kelas_jabatan')"></i></th>
                                <th @click="sortBy('grade')"
                                    class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap small">
                                    Grade
                                    <i :class="sortIcon('grade')"></i>
                                </th>
                                </th>
                                <th @click="sortBy('kuota')"
                                    class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap">Kuota
                                    <i :class="sortIcon('kuota')"></i>
                                </th>

                                @can(abilities: 'admin')
                                    <th class="text-slate-500 font-semibold text-nowrap">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in paginatedFormasi" :key="item.id">
                                <tr class="text-slate-700">
                                    <td x-text="(currentPage - 1) * itemsPerPage + index + 1"></td>
                                    <td><span class="badge bg-info-subtle text-info-emphasis rounded-pill"
                                            x-text="item.kode_jabatan"></span></td>
                                    <td x-text="item.unit_deputy_egm || '-'"></td>
                                    <td x-text="item.unit_assistant_deputy || '-'"></td>
                                    <td x-text="item.unit_division_head || '-'"></td>
                                    <td x-text="item.unit_department_head || '-'"></td>
                                    <td x-text="item.lokasi_kerja"></td>
                                    <td x-text="item.unit_kerja"></td>
                                    <td x-text="item.jabatan"></td>
                                    <td x-text="item.kelompok_kelas_jabatan"></td>
                                    <td x-text="item.grade"></td>
                                    <td><span class="badge bg-success-subtle text-success-emphasis rounded-pill"
                                            x-text="item.kuota"></span></td>
                                    @can(abilities: 'admin')
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a :href="`/formasi/${item.id}/edit`" class="btn btn-sm btn-outline-warning"
                                                    title="Edit">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus"
                                                    data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal"
                                                    @click="deleteUrl = `/formasi/${item.id}`">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </div>
                                        </td>
                                    @endcan
                                </tr>
                            </template>
                            <tr x-show="!paginatedFormasi.length">
                                <td colspan="12" class="text-center text-muted py-5">
                                    <span x-show="formasi.length > 0">Data tidak ditemukan.</span>
                                    <span x-show="formasi.length === 0">Belum ada data formasi.</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                    <div class="text-slate-600 small">
                        Menampilkan <span
                            x-text="Math.min((currentPage - 1) * itemsPerPage + 1, sortedFormasi.length)"></span>
                        sampai <span x-text="Math.min(currentPage * itemsPerPage, sortedFormasi.length)"></span>
                        dari <span x-text="sortedFormasi.length"></span> data
                    </div>
                    <nav x-show="totalPages > 1">
                        <ul class="pagination mb-0">
                            <li class="page-item" :class="{ 'disabled': currentPage === 1 }">
                                <a class="page-link" href="#"
                                    @click.prevent="changePage(currentPage - 1)">Previous</a>
                            </li>
                            <template x-for="page in pages">
                                <li class="page-item"
                                    :class="{ 'active': page === currentPage, 'disabled': page === '...' }">
                                    <a class="page-link" href="#"
                                        @click.prevent="if (page !== '...') changePage(page)" x-text="page"></a>
                                </li>
                            </template>
                            <li class="page-item" :class="{ 'disabled': currentPage === totalPages }">
                                <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Modal Konfirmasi Hapus -->
            <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="deleteModalLabel">
                                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Konfirmasi Hapus Data
                            </h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Apakah Anda benar-benar yakin ingin menghapus data formasi ini? Proses ini tidak dapat
                            diurungkan.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <form id="deleteForm" :action="deleteUrl" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> {{-- End formasi-container --}}
@endsection

@push('body-scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('formasiTable', (initialFormasi = []) => ({
                formasi: initialFormasi,
                searchTerm: '',
                sortColumn: 'created_at',
                sortDirection: 'desc',
                itemsPerPage: 10,
                currentPage: 1,
                deleteUrl: '',

                get filteredFormasi() {
                    if (this.searchTerm === '') return this.formasi;
                    const term = this.searchTerm.toLowerCase();
                    return this.formasi.filter(item =>
                        Object.values(item).some(value =>
                            String(value).toLowerCase().includes(term)
                        )
                    );
                },
                get sortedFormasi() {
                    return [...this.filteredFormasi].sort((a, b) => {
                        let colA = a[this.sortColumn];
                        let colB = b[this.sortColumn];

                        // Handle null/undefined
                        if (colA == null && colB == null) return 0;
                        if (colA == null) return this.sortDirection === 'asc' ? -1 : 1;
                        if (colB == null) return this.sortDirection === 'asc' ? 1 : -1;

                        // Cek apakah keduanya angka
                        const numA = Number(colA);
                        const numB = Number(colB);

                        let comparison = 0;
                        if (!isNaN(numA) && !isNaN(numB)) {
                            // Bandingkan sebagai angka
                            comparison = numA - numB;
                        } else {
                            // Bandingkan sebagai string
                            comparison = String(colA).localeCompare(String(colB));
                        }

                        return this.sortDirection === 'asc' ? comparison : -comparison;
                    });
                },
                get paginatedFormasi() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    const end = start + this.itemsPerPage;
                    return this.sortedFormasi.slice(start, end);
                },
                get totalPages() {
                    return Math.ceil(this.sortedFormasi.length / this.itemsPerPage);
                },
                get pages() {
                    const maxPages = 7;
                    const total = this.totalPages;
                    const current = this.currentPage;
                    if (total <= maxPages) {
                        return Array.from({
                            length: total
                        }, (_, i) => i + 1);
                    }
                    const pagesArray = [1];
                    let start = Math.max(2, current - 2);
                    let end = Math.min(total - 1, current + 2);
                    if (current < 4) {
                        end = 5;
                    }
                    if (current > total - 3) {
                        start = total - 4;
                    }
                    if (start > 2) pagesArray.push('...');
                    for (let i = start; i <= end; i++) {
                        pagesArray.push(i);
                    }
                    if (end < total - 1) pagesArray.push('...');
                    pagesArray.push(total);
                    return pagesArray;
                },
                sortBy(column) {
                    if (this.sortColumn === column) {
                        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortColumn = column;
                        this.sortDirection = 'asc';
                    }
                },
                sortIcon(column) {
                    if (this.sortColumn !== column) return 'bi bi-arrow-down-up opacity-25';
                    return this.sortDirection === 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down';
                },
                changePage(page) {
                    if (page < 1 || page > this.totalPages) return;
                    this.currentPage = page;
                    document.querySelector('main')?.scrollTo(0, 0);
                },
                init() {
                    this.$watch('searchTerm', () => this.currentPage = 1);
                    this.$watch('itemsPerPage', () => this.currentPage = 1);
                }
            }));
        });
    </script>
@endpush
