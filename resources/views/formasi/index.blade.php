@extends('layouts.app')

@section('title', 'Data Formasi')
@section('header-title', 'Data Formasi')

@section('content')
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
    <div class="bg-white rounded-xl shadow-sm" x-data="formasiTable({{ $formasi->toJson() ?? '[]' }})">
        <div class="p-4 sm:p-5">
            {{-- Header Tabel --}}
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    @can('admin')
                    <a href="{{ route('formasi.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span class="text-nowrap">Tambah Data</span>
                    </a>
                    @endcan    
                    <a href="{{ route('formasi.export') }}" class="btn btn-outline-success d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                        <span class="text-nowrap">Download Data Excel</span>
                    </a>
                </div>
                {{-- Sisi Kanan: Filter dan Search --}}
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <label for="itemsPerPage" class="form-label text-nowrap mb-0 text-slate-600">Tampilkan</label>
                        <select id="itemsPerPage" class="form-select form-select-sm" style="width: auto;"
                            x-model.number="itemsPerPage">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="1000">1000</option>
                        </select>
                        <span class="text-slate-600 text-nowrap">data</span>
                    </div>
                    <div style="width: 250px;">
                        <input type="text" class="form-control" placeholder="Cari formasi..."
                            x-model.debounce.300ms="searchTerm">
                    </div>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="text-slate-500 font-semibold text-nowrap">No</th>
                            <th @click="sortBy('kode_jabatan')"
                                class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap">Kode
                                Jabatan <i :class="sortIcon('kode_jabatan')"></i></th>
                            <th @click="sortBy('lokasi')"
                                class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap">Lokasi <i
                                    :class="sortIcon('lokasi')"></i></th>
                            <th @click="sortBy('unit')"
                                class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap">Unit <i
                                    :class="sortIcon('unit')"></i></th>
                            <th @click="sortBy('jabatan')"
                                class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap">Jabatan <i
                                    :class="sortIcon('jabatan')"></i></th>
                            <th @click="sortBy('kelompok_kelas_jabatan')"
                                class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap">KKJ <i
                                    :class="sortIcon('kelompok_kelas_jabatan')"></i></th>
                            <th @click="sortBy('grade')"
                                class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap">Grade <i
                                    :class="sortIcon('grade')"></i></th>
                            <th @click="sortBy('kuota')"
                                class="text-slate-500 font-semibold cursor-pointer user-select-none text-nowrap">Kuota <i
                                    :class="sortIcon('kuota')"></i></th>
                        
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
                                <td x-text="item.lokasi"></td>
                                <td x-text="item.unit"></td>
                                <td x-text="item.jabatan"></td>
                                <td x-text="item.kelompok_kelas_jabatan"></td>
                                <td><span class="badge bg-primary-subtle text-primary-emphasis rounded-pill"
                                        x-text="item.grade"></span></td>
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
                            <td colspan="8" class="text-center text-muted py-5">
                                <span x-show="formasi.length > 0">Data tidak ditemukan.</span>
                                <span x-show="formasi.length === 0">Belum ada data formasi.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                <div class="text-slate-600">
                    Menampilkan <span x-text="Math.min((currentPage - 1) * itemsPerPage + 1, sortedFormasi.length)"></span>
                    sampai <span x-text="Math.min(currentPage * itemsPerPage, sortedFormasi.length)"></span>
                    dari <span x-text="sortedFormasi.length"></span> data
                </div>
                <nav x-show="totalPages > 1">
                    <ul class="pagination mb-0">
                        <li class="page-item" :class="{ 'disabled': currentPage === 1 }">
                            <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Previous</a>
                        </li>
                        <template x-for="page in pages">
                            <li class="page-item"
                                :class="{ 'active': page === currentPage, 'disabled': page === '...' }">
                                <a class="page-link" href="#" @click.prevent="if (page !== '...') changePage(page)"
                                    x-text="page"></a>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Apakah Anda benar-benar yakin ingin menghapus data formasi ini? Proses ini tidak dapat diurungkan.
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
                        const colA = a[this.sortColumn],
                            colB = b[this.sortColumn];
                        let comparison = 0;
                        if (colA > colB) comparison = 1;
                        else if (colA < colB) comparison = -1;
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
