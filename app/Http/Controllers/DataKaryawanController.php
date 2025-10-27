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
            'nik' => 'required|string|unique:data_karyawan,nik',
            'nama' => 'required|string|max:255',
            'gender' => 'required|string|in:Laki-laki,Perempuan',
            'formasi_select' => 'required|exists:formasi,id',
            'status_kepegawaian' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'pendidikan_terakhir' => 'required|string',
            'tmt' => 'required|date',
        ]);

        $formasi = \App\Models\Formasi::findOrFail($request->formasi_select);

        // Konversi format tanggal dari Y-m-d ke d/m/Y untuk konsistensi database
        $tanggal_lahir = $validatedData['tanggal_lahir'];
        $tmt = $validatedData['tmt'];
        try {
            $tanggal_lahir = \Carbon\Carbon::createFromFormat('Y-m-d', $tanggal_lahir)->format('d/m/Y');
        } catch (\Exception $e) {
        }
        try {
            $tmt = \Carbon\Carbon::createFromFormat('Y-m-d', $tmt)->format('d/m/Y');
        } catch (\Exception $e) {
        }

        $data = [
            'nik' => $validatedData['nik'],
            'nama' => $validatedData['nama'],
            'gender' => $validatedData['gender'],
            'kode_jabatan' => $formasi->kode_jabatan,
            'lokasi' => $formasi->lokasi,
            'unit' => $formasi->unit,
            'jabatan' => $formasi->jabatan,
            'kelompok_kelas_jabatan' => $formasi->kelompok_kelas_jabatan,
            'grade' => $formasi->grade,
            'status_kepegawaian' => $validatedData['status_kepegawaian'],
            'tanggal_lahir' => $tanggal_lahir,
            'pendidikan_terakhir' => $validatedData['pendidikan_terakhir'],
            'tmt' => $tmt,
        ];

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
            'nik' => ['required', 'string', Rule::unique('data_karyawan')->ignore($dataKaryawan->id)],
            'nama' => 'required|string|max:255',
            'gender' => 'required|string|in:Laki-laki,Perempuan',
            'formasi_select' => 'required|exists:formasi,id',
            'status_kepegawaian' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'pendidikan_terakhir' => 'required|string',
            'tmt' => 'required|date',
        ]);

        $formasi = \App\Models\Formasi::findOrFail($request->formasi_select);

        $tanggal_lahir = $validatedData['tanggal_lahir'];
        $tmt = $validatedData['tmt'];
        try {
            $tanggal_lahir = \Carbon\Carbon::createFromFormat('Y-m-d', $tanggal_lahir)->format('d/m/Y');
        } catch (\Exception $e) {
        }
        try {
            $tmt = \Carbon\Carbon::createFromFormat('Y-m-d', $tmt)->format('d/m/Y');
        } catch (\Exception $e) {
        }

        $data = [
            'nik' => $validatedData['nik'],
            'nama' => $validatedData['nama'],
            'gender' => $validatedData['gender'],
            'kode_jabatan' => $formasi->kode_jabatan,
            'lokasi' => $formasi->lokasi,
            'unit' => $formasi->unit,
            'jabatan' => $formasi->jabatan,
            'kelompok_kelas_jabatan' => $formasi->kelompok_kelas_jabatan,
            'grade' => $formasi->grade,
            'status_kepegawaian' => $validatedData['status_kepegawaian'],
            'tanggal_lahir' => $tanggal_lahir,
            'pendidikan_terakhir' => $validatedData['pendidikan_terakhir'],
            'tmt' => $tmt,
        ];

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

