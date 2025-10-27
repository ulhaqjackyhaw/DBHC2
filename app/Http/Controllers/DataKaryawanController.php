<?php

namespace App\Http\Controllers;

use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DataKaryawanImport;
use App\Exports\DataKaryawanExport;
use App\Exports\DataKaryawanTemplateExport;
use Illuminate\Validation\Rule;

class DataKaryawanController extends Controller
{
    public function index()
    {
        $employees = DataKaryawan::latest()->get();
        return view('karyawan.index', compact('employees'));
    }

    public function create()
    {
        $formasiList = \App\Models\Formasi::all();
        return view('karyawan.create', compact('formasiList'));
    }

    public function show(DataKaryawan $dataKaryawan)
    {
        $employee = $dataKaryawan; // untuk kompatibilitas dengan view yang sudah ada
        return view('karyawan.show', compact('employee'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // Required fields
            'nik' => 'required|string|unique:data_karyawan,nik',
            'nama' => 'required|string|max:255',

            // Optional basic fields
            'jenis_kelamin' => 'nullable|string|in:Laki-laki,Perempuan',
            'formasi_select' => 'nullable|exists:formasi,id',
            'status' => 'nullable|string',
            'asal_instansi' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'pendidikan_diakui' => 'nullable|string',
            'tmt' => 'nullable|date',

            // Unit & Struktur
            'unit_deputy_egm' => 'nullable|string',
            'unit_assistant_deputy' => 'nullable|string',
            'unit_division_head' => 'nullable|string',
            'unit_department_head' => 'nullable|string',

            // Info Jabatan
            'tmt_jabatan' => 'nullable|date',
            'job_grade' => 'nullable|string',
            'person_grade' => 'nullable|string',
            'awal_lokasi_kerja' => 'nullable|string',
            'status_jabatan' => 'nullable|string',
            'sub_status' => 'nullable|string',
            'instansi' => 'nullable|string',
            'fungsi_kontrak_os' => 'nullable|string',

            // Data Personal
            'agama' => 'nullable|string',
            'status_pernikahan' => 'nullable|string',
            'jurusan' => 'nullable|string',
            'pendidikan_dimiliki' => 'nullable|string',
            'no_ktp' => 'nullable|string',
            'alamat_ktp' => 'nullable|string',
            'no_kontrak' => 'nullable|string',
            'generasi' => 'nullable|string',
            'rencana_mpp' => 'nullable|string',
            'rencana_pensiun' => 'nullable|string',
            'tmt_karyawan' => 'nullable|date',
            'masa_kerja' => 'nullable|string',
            'kategori' => 'nullable|string',
            'nilai_npi_2022' => 'nullable|numeric',
            'usia' => 'nullable|integer',

            // Karir & Klasifikasi
            'keluarga_jabatan' => 'nullable|string',
            'sub_keluarga_jabatan' => 'nullable|string',
            'fungsi_jabatan' => 'nullable|string',
            'fungsi_pekerjaan' => 'nullable|string',
            'jalur_karir' => 'nullable|string',
            'jenjang_karir' => 'nullable|string',
            'tmt_kj_tertinggi' => 'nullable|date',
            'masa_kj_tertinggi_tahun' => 'nullable|integer',
            'no_sk_jabatan_terakhir' => 'nullable|string',
            'tgl_sk_jabatan_terakhir' => 'nullable|date',
            'kpi_2023' => 'nullable|numeric',
            'kriteria' => 'nullable|string',

            // Lisensi
            'lisence_dimiliki' => 'nullable|string',
            'rating' => 'nullable|string',
            'no_stkp' => 'nullable|string',
            'masa_berlaku' => 'nullable|date',
            'lisence_dibayarkan_januari' => 'nullable|string',
            'cek_lisence_serkom' => 'nullable|string',

            // Kontak
            'email' => 'nullable|email',
            'no_hp' => 'nullable|string',
        ]);

        // Data dasar
        $data = [
            'nik' => $validatedData['nik'],
            'nama' => $validatedData['nama'],
        ];

        // Jika ada formasi dipilih, tambahkan data formasi
        if ($request->filled('formasi_select')) {
            $formasi = \App\Models\Formasi::findOrFail($request->formasi_select);
            $data['kode_jabatan'] = $formasi->kode_jabatan;
            $data['lokasi_kerja'] = $formasi->lokasi;
            $data['unit_kerja'] = $formasi->unit;
            $data['jabatan'] = $formasi->jabatan;
            $data['kelompok_kelas_jabatan'] = $formasi->kelompok_kelas_jabatan;
            $data['grade'] = $formasi->grade;
        }

        // Konversi semua field tanggal dari Y-m-d ke d/m/Y
        $dateFields = [
            'tanggal_lahir',
            'tmt',
            'tmt_jabatan',
            'tmt_karyawan',
            'tmt_kj_tertinggi',
            'tgl_sk_jabatan_terakhir',
            'masa_berlaku'
        ];

        foreach ($dateFields as $field) {
            if ($request->filled($field)) {
                try {
                    $data[$field] = \Carbon\Carbon::createFromFormat('Y-m-d', $validatedData[$field])->format('d/m/Y');
                } catch (\Exception $e) {
                    $data[$field] = $validatedData[$field];
                }
            }
        }

        // Tambahkan field lainnya yang tidak kosong
        $otherFields = [
            'jenis_kelamin',
            'status',
            'asal_instansi',
            'pendidikan_diakui',
            'unit_deputy_egm',
            'unit_assistant_deputy',
            'unit_division_head',
            'unit_department_head',
            'job_grade',
            'person_grade',
            'awal_lokasi_kerja',
            'status_jabatan',
            'sub_status',
            'instansi',
            'fungsi_kontrak_os',
            'agama',
            'status_pernikahan',
            'jurusan',
            'pendidikan_dimiliki',
            'no_ktp',
            'alamat_ktp',
            'no_kontrak',
            'generasi',
            'rencana_mpp',
            'rencana_pensiun',
            'masa_kerja',
            'kategori',
            'nilai_npi_2022',
            'usia',
            'keluarga_jabatan',
            'sub_keluarga_jabatan',
            'fungsi_jabatan',
            'fungsi_pekerjaan',
            'jalur_karir',
            'jenjang_karir',
            'masa_kj_tertinggi_tahun',
            'no_sk_jabatan_terakhir',
            'kpi_2023',
            'kriteria',
            'lisence_dimiliki',
            'rating',
            'no_stkp',
            'lisence_dibayarkan_januari',
            'cek_lisence_serkom',
            'email',
            'no_hp'
        ];

        foreach ($otherFields as $field) {
            if ($request->filled($field)) {
                $data[$field] = $validatedData[$field];
            }
        }

        DataKaryawan::create($data);
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function edit(DataKaryawan $dataKaryawan)
    {
        $karyawan = $dataKaryawan;
        $formasiList = \App\Models\Formasi::all();
        return view('karyawan.edit', compact('karyawan', 'formasiList'));
    }

    // PERBAIKAN: Menyamakan nama variabel menjadi '$dataKaryawan'
    public function update(Request $request, DataKaryawan $dataKaryawan)
    {
        $validatedData = $request->validate([
            // Required fields
            'nik' => ['required', 'string', Rule::unique('data_karyawan')->ignore($dataKaryawan->id)],
            'nama' => 'required|string|max:255',

            // Optional basic fields
            'jenis_kelamin' => 'nullable|string|in:Laki-laki,Perempuan',
            'formasi_select' => 'nullable|exists:formasi,id',
            'status' => 'nullable|string',
            'asal_instansi' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'pendidikan_diakui' => 'nullable|string',
            'tmt' => 'nullable|date',

            // Unit & Struktur
            'unit_deputy_egm' => 'nullable|string',
            'unit_assistant_deputy' => 'nullable|string',
            'unit_division_head' => 'nullable|string',
            'unit_department_head' => 'nullable|string',

            // Info Jabatan
            'tmt_jabatan' => 'nullable|date',
            'job_grade' => 'nullable|string',
            'person_grade' => 'nullable|string',
            'awal_lokasi_kerja' => 'nullable|string',
            'status_jabatan' => 'nullable|string',
            'sub_status' => 'nullable|string',
            'instansi' => 'nullable|string',
            'fungsi_kontrak_os' => 'nullable|string',

            // Data Personal
            'agama' => 'nullable|string',
            'status_pernikahan' => 'nullable|string',
            'jurusan' => 'nullable|string',
            'pendidikan_dimiliki' => 'nullable|string',
            'no_ktp' => 'nullable|string',
            'alamat_ktp' => 'nullable|string',
            'no_kontrak' => 'nullable|string',
            'generasi' => 'nullable|string',
            'rencana_mpp' => 'nullable|string',
            'rencana_pensiun' => 'nullable|string',
            'tmt_karyawan' => 'nullable|date',
            'masa_kerja' => 'nullable|string',
            'kategori' => 'nullable|string',
            'nilai_npi_2022' => 'nullable|numeric',
            'usia' => 'nullable|integer',

            // Karir & Klasifikasi
            'keluarga_jabatan' => 'nullable|string',
            'sub_keluarga_jabatan' => 'nullable|string',
            'fungsi_jabatan' => 'nullable|string',
            'fungsi_pekerjaan' => 'nullable|string',
            'jalur_karir' => 'nullable|string',
            'jenjang_karir' => 'nullable|string',
            'tmt_kj_tertinggi' => 'nullable|date',
            'masa_kj_tertinggi_tahun' => 'nullable|integer',
            'no_sk_jabatan_terakhir' => 'nullable|string',
            'tgl_sk_jabatan_terakhir' => 'nullable|date',
            'kpi_2023' => 'nullable|numeric',
            'kriteria' => 'nullable|string',

            // Lisensi
            'lisence_dimiliki' => 'nullable|string',
            'rating' => 'nullable|string',
            'no_stkp' => 'nullable|string',
            'masa_berlaku' => 'nullable|date',
            'lisence_dibayarkan_januari' => 'nullable|string',
            'cek_lisence_serkom' => 'nullable|string',

            // Kontak
            'email' => 'nullable|email',
            'no_hp' => 'nullable|string',
        ]);

        // Data dasar
        $data = [
            'nik' => $validatedData['nik'],
            'nama' => $validatedData['nama'],
        ];

        // Jika ada formasi dipilih, update data formasi
        if ($request->filled('formasi_select')) {
            $formasi = \App\Models\Formasi::findOrFail($request->formasi_select);
            $data['kode_jabatan'] = $formasi->kode_jabatan;
            $data['lokasi_kerja'] = $formasi->lokasi;
            $data['unit_kerja'] = $formasi->unit;
            $data['jabatan'] = $formasi->jabatan;
            $data['kelompok_kelas_jabatan'] = $formasi->kelompok_kelas_jabatan;
            $data['grade'] = $formasi->grade;
        }

        // Konversi semua field tanggal dari Y-m-d ke d/m/Y
        $dateFields = [
            'tanggal_lahir',
            'tmt',
            'tmt_jabatan',
            'tmt_karyawan',
            'tmt_kj_tertinggi',
            'tgl_sk_jabatan_terakhir',
            'masa_berlaku'
        ];

        foreach ($dateFields as $field) {
            if ($request->filled($field)) {
                try {
                    $data[$field] = \Carbon\Carbon::createFromFormat('Y-m-d', $validatedData[$field])->format('d/m/Y');
                } catch (\Exception $e) {
                    $data[$field] = $validatedData[$field];
                }
            }
        }

        // Tambahkan field lainnya yang tidak kosong
        $otherFields = [
            'jenis_kelamin',
            'status',
            'asal_instansi',
            'pendidikan_diakui',
            'unit_deputy_egm',
            'unit_assistant_deputy',
            'unit_division_head',
            'unit_department_head',
            'job_grade',
            'person_grade',
            'awal_lokasi_kerja',
            'status_jabatan',
            'sub_status',
            'instansi',
            'fungsi_kontrak_os',
            'agama',
            'status_pernikahan',
            'jurusan',
            'pendidikan_dimiliki',
            'pendidikan_dimiliki',
            'no_ktp',
            'alamat_ktp',
            'no_kontrak',
            'generasi',
            'rencana_mpp',
            'rencana_pensiun',
            'masa_kerja',
            'kategori',
            'nilai_npi_2022',
            'usia',
            'keluarga_jabatan',
            'sub_keluarga_jabatan',
            'fungsi_jabatan',
            'fungsi_pekerjaan',
            'jalur_karir',
            'jenjang_karir',
            'masa_kj_tertinggi_tahun',
            'no_sk_jabatan_terakhir',
            'kpi_2023',
            'kriteria',
            'lisence_dimiliki',
            'rating',
            'no_stkp',
            'lisence_dibayarkan_januari',
            'cek_lisence_serkom',
            'email',
            'no_hp'
        ];

        foreach ($otherFields as $field) {
            if ($request->filled($field)) {
                $data[$field] = $validatedData[$field];
            }
        }

        $dataKaryawan->update($data);
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    // PERBAIKAN: Menyamakan nama variabel menjadi '$dataKaryawan'
    public function destroy(DataKaryawan $dataKaryawan)
    {
        $dataKaryawan->delete();
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil dihapus.');
    }

    public function importAdd(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);

        try {
            // Tingkatkan memory limit dan waktu eksekusi untuk file besar
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', 300); // 5 menit

            Excel::import(new DataKaryawanImport, $request->file('file'));
            return redirect()->route('karyawan.index')->with('success', 'Data berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function importReplace(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);

        try {
            // Tingkatkan memory limit dan waktu eksekusi untuk file besar
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', 300); // 5 menit

            DataKaryawan::query()->delete();
            Excel::import(new DataKaryawanImport, $request->file('file'));
            return redirect()->route('karyawan.index')->with('success', 'Semua data berhasil diganti.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new DataKaryawanExport, 'data_karyawan_' . date('Y-m-d') . '.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new DataKaryawanTemplateExport, 'template_data_karyawan.xlsx');
    }
}

