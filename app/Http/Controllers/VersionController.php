<?php

namespace App\Http\Controllers;

use App\Models\DataKaryawan;
use App\Models\EmployeeHistory;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class VersionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|digits:4',
            'month' => 'nullable|integer|between:1,12',
            'day' => 'nullable|date_format:Y-m-d',
        ]);

        $query = Version::query();

        if ($request->filled('year'))
            $query->whereYear('created_at', $request->year);
        if ($request->filled('month'))
            $query->whereMonth('created_at', $request->month);
        if ($request->filled('day'))
            $query->whereDate('created_at', $request->day);

        $versions = $query->withCount('history')->latest()->paginate(15)->appends($request->query());
        $years = Version::selectRaw('YEAR(created_at) as year')->distinct()->orderBy('year', 'desc')->pluck('year');

        return view('versions.index', [
            'versions' => $versions,
            'years' => $years,
            'filters' => $request->only(['year', 'month', 'day']),
        ]);
    }

    public function store(Request $request)
    {
        // PERBAIKAN 1: Validasi diubah menjadi 'nullable' agar deskripsi boleh kosong.
        $request->validate(['description' => 'nullable|string|max:255']);

        try {
            DB::transaction(function () use ($request) {
                // Memberikan deskripsi default jika input kosong, agar data tetap bermakna.
                $description = $request->input('description') ?? 'DATABASE KEPEGAWAIAN ' . now()->format('d-m-Y H:i:s');

                $version = Version::create(['description' => $description]);

                // PERBAIKAN 2 (KUNCI PERFORMA): Menggunakan chunkById untuk memproses data
                // per 500 baris agar tidak membebani memori server.
                DataKaryawan::query()->chunkById(500, function ($employees) use ($version) {
                    $historyData = $employees->map(function ($employee) use ($version) {
                        return [
                            'version_id' => $version->id,
                            'nik' => $employee->nik,
                            'nama' => $employee->nama,
                            'gender' => $employee->gender,
                            'kode_jabatan' => $employee->kode_jabatan,
                            'lokasi' => $employee->lokasi,
                            'unit' => $employee->unit,
                            'jabatan' => $employee->jabatan,
                            'kelompok_kelas_jabatan' => $employee->kelompok_kelas_jabatan,
                            'grade' => $employee->grade,
                            'status_kepegawaian' => $employee->status_kepegawaian,
                            'asal_instansi' => $employee->asal_instansi,
                            'tanggal_lahir' => $employee->tanggal_lahir,
                            'pendidikan_terakhir' => $employee->pendidikan_terakhir,
                            'tmt' => $employee->tmt,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    })->toArray();

                    if (!empty($historyData)) {
                        EmployeeHistory::insert($historyData);
                    }
                });
            });
        } catch (\Exception $e) {
            Log::error('Gagal membuat versi: ' . $e->getMessage());
            return redirect()->route('karyawan.index')->with('error', 'Terjadi kesalahan saat membuat versi data.');
        }

        return redirect()->route('karyawan.index')->with('success', 'Versi data berhasil disimpan!');
    }

    public function restore(Version $version)
    {
        if (!$version->history()->exists()) {
            return redirect()->route('versions.index')->with('error', 'Versi ini tidak memiliki data untuk dipulihkan.');
        }

        DB::beginTransaction();
        try {
            Schema::disableForeignKeyConstraints();
            DataKaryawan::query()->delete();

            EmployeeHistory::where('version_id', $version->id)
                ->chunkById(500, function ($histories) {
                    $restoredData = $histories->map(function ($history) {
                        return [
                            'nik' => $history->nik,
                            'nama' => $history->nama,
                            'gender' => $history->gender,
                            'kode_jabatan' => $history->kode_jabatan,
                            'lokasi' => $history->lokasi,
                            'unit' => $history->unit,
                            'jabatan' => $history->jabatan,
                            'kelompok_kelas_jabatan' => $history->kelompok_kelas_jabatan,
                            'grade' => $history->grade,
                            'status_kepegawaian' => $history->status_kepegawaian,
                            'asal_instansi' => $history->asal_instansi,
                            'tanggal_lahir' => $history->tanggal_lahir,
                            'pendidikan_terakhir' => $history->pendidikan_terakhir,
                            'tmt' => $history->tmt,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    })->toArray();

                    if (!empty($restoredData)) {
                        DB::table('data_karyawan')->insert($restoredData);
                    }
                });

            Schema::enableForeignKeyConstraints();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();
            Log::error('Gagal memulihkan versi: ' . $e->getMessage());
            return redirect()->route('versions.index')->with('error', 'Gagal memulihkan data. Error: ' . $e->getMessage());
        }

        return redirect()->route('versions.index')->with('success', "Data berhasil dipulihkan dari versi: '{$version->description}'.");
    }

    public function download(Version $version)
    {
        // Check if version has data
        if (!$version->history()->exists()) {
            return redirect()->route('versions.index')->with('error', 'Versi ini tidak memiliki data untuk diunduh.');
        }

        // Create filename with version info
        $filename = 'Data_Karyawan_' . str_replace(['/', ' ', ':', '-'], '_', $version->description) . '_' . $version->created_at->format('Y_m_d') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\VersionDataExport($version->id), $filename);
    }

    public function destroy(Version $version)
    {
        $version->delete();
        return redirect()->route('versions.index')->with('success', 'Versi histori berhasil dihapus.');
    }
}