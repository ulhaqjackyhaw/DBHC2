<?php

namespace App\Imports;

use App\Models\DataKaryawan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class DataKaryawanImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsEmptyRows
{
    use Importable;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Handle date conversion for Excel serial dates
        $tanggalLahir = $row['tanggal_lahir'] ?? $row['TANGGAL LAHIR'];
        if (is_numeric($tanggalLahir)) {
            // Convert Excel serial date to date string
            $tanggalLahir = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalLahir)->format('d/m/Y');
        }

        $tmt = $row['tmt'] ?? $row['TMT'];
        if (is_numeric($tmt)) {
            // Convert Excel serial date to date string
            $tmt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tmt)->format('d/m/Y');
        }

        return new DataKaryawan([
            'nik' => (string) ($row['nik'] ?? $row['NIK']),
            'nama' => $row['nama'] ?? $row['Nama'],
            'gender' => $row['gender'] ?? $row['GENDER'],
            'kode_jabatan' => $row['kode_jabatan'] ?? $row['kode jabatan'] ?? $row['KODE JABATAN'],
            'lokasi' => $row['lokasi'] ?? $row['LOKASI'],
            'unit' => $row['unit'] ?? $row['UNIT'],
            'jabatan' => $row['jabatan'] ?? $row['JABATAN'],
            'kelompok_kelas_jabatan' => $row['kelompok_kelas_jabatan'] ?? $row['kelompok kelas jabatan'] ?? $row['KELOMPOK KELAS JABATAN'],
            'grade' => (string) ($row['grade'] ?? $row['GRADE']),
            'status_kepegawaian' => $row['status_kepegawaian'] ?? $row['status kepegawaian'] ?? $row['STATUS KEPEGAWAIAN'],
            'asal_instansi' => $row['asal_instansi'] ?? $row['asal instansi'] ?? $row['ASAL INSTANSI'] ?? null,
            'tanggal_lahir' => $tanggalLahir,
            'pendidikan_terakhir' => $row['pendidikan_terakhir'] ?? $row['pendidikan terakhir'] ?? $row['PENDIDIKAN TERAKHIR'],
            'tmt' => $tmt,
        ]);
    }



    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 200; // Kurangi agar tidak error terlalu banyak placeholders
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 200; // Kurangi agar tidak error terlalu banyak placeholders
    }
}