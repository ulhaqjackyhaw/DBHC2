<?php

namespace App\Http\Controllers;

use App\Models\Formasi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FormasiImport;
use App\Exports\FormasiExport;
use App\Exports\FormasiTemplateExport;
use Illuminate\Validation\Rule;

class FormasiController extends Controller
{
    public function index()
    {
        $formasi = Formasi::latest()->get();
        return view('formasi.index', compact('formasi'));
    }

    public function create()
    {
        return view('formasi.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'kode_jabatan' => 'required|string',
            'lokasi' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'kelompok_kelas_jabatan' => 'required|string|max:255',
            'grade' => 'required|string|max:255',
            'kuota' => 'required|integer|min:1',
        ]);

        // Cek unique kombinasi kode_jabatan, lokasi, unit
        if (
            Formasi::where('kode_jabatan', $validatedData['kode_jabatan'])
                ->where('lokasi', $validatedData['lokasi'])
                ->where('unit', $validatedData['unit'])
                ->exists()
        ) {
            return redirect()->back()->with('error', 'Formasi dengan kombinasi kode jabatan, lokasi, dan unit sudah ada.');
        }

        Formasi::create($validatedData);
        return redirect()->route('formasi.index')->with('success', 'Data formasi berhasil ditambahkan.');
    }

    public function show(Formasi $formasi)
    {
        return view('formasi.show', compact('formasi'));
    }

    public function edit(Formasi $formasi)
    {
        return view('formasi.edit', compact('formasi'));
    }

    public function update(Request $request, Formasi $formasi)
    {
        $validatedData = $request->validate([
            'kode_jabatan' => ['required', 'string'],
            'lokasi' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'kelompok_kelas_jabatan' => 'required|string|max:255',
            'grade' => 'required|string|max:255',
            'kuota' => 'required|integer|min:1',
        ]);

        // Cek unique kombinasi kode_jabatan, lokasi, unit (kecuali diri sendiri)
        if (
            Formasi::where('kode_jabatan', $validatedData['kode_jabatan'])
                ->where('lokasi', $validatedData['lokasi'])
                ->where('unit', $validatedData['unit'])
                ->where('id', '!=', $formasi->id)
                ->exists()
        ) {
            return redirect()->back()->with('error', 'Formasi dengan kombinasi kode jabatan, lokasi, dan unit sudah ada.');
        }

        $formasi->update($validatedData);
        return redirect()->route('formasi.index')->with('success', 'Data formasi berhasil diperbarui.');
    }

    public function destroy(Formasi $formasi)
    {
        $formasi->delete();
        return redirect()->route('formasi.index')->with('success', 'Data formasi berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new FormasiExport, 'formasi_' . date('Y-m-d') . '.xlsx');
    }

    public function importAdd(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);

        try {
            // OPTIMISASI UNTUK FILE BESAR: Tanpa batasan jumlah baris
            // - Memory limit 1GB untuk menangani puluhan ribu baris
            // - Execution time 5 menit untuk proses import yang lama
            // - Batch size 5000 untuk performa optimal
            // - Chunk reading untuk efisiensi memory
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', 300); // 5 menit

            Excel::import(new FormasiImport, $request->file('file'));
            return redirect()->route('formasi.index')->with('success', 'Data formasi berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function importReplace(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);

        try {
            // OPTIMISASI UNTUK FILE BESAR: Tanpa batasan jumlah baris
            // - Memory limit 1GB untuk menangani puluhan ribu baris  
            // - Execution time 5 menit untuk proses import yang lama
            // - Hapus semua data lama kemudian import data baru
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', 300); // 5 menit

            Formasi::query()->delete();
            Excel::import(new FormasiImport, $request->file('file'));
            return redirect()->route('formasi.index')->with('success', 'Semua data formasi berhasil diganti.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new FormasiTemplateExport, 'template_formasi.xlsx');
    }
}
