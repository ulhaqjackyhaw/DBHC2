<?php

namespace App\Imports;

use App\Models\Formasi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class FormasiImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsEmptyRows
{
    use Importable;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Formasi([
            'kode_jabatan' => $row['kode_jabatan'] ?? $row['kode jabatan'] ?? $row['KODE JABATAN'] ?? $row['Kode Jabatan'],
            'lokasi' => $row['lokasi'] ?? $row['LOKASI'] ?? $row['Lokasi'],
            'unit' => $row['unit'] ?? $row['UNIT'] ?? $row['Unit'],
            'jabatan' => $row['jabatan'] ?? $row['JABATAN'] ?? $row['Jabatan'],
            'kelompok_kelas_jabatan' => $row['kelompok_kelas_jabatan'] ?? $row['kelompok kelas jabatan'] ?? $row['KELOMPOK KELAS JABATAN'] ?? $row['Kelompok Kelas Jabatan'],
            'grade' => (string) ($row['grade'] ?? $row['GRADE'] ?? $row['Grade']),
            'kuota' => isset($row['kuota']) ? (int) $row['kuota'] : 1,
        ]);
    }



    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 5000; // Tingkatkan untuk performa yang lebih baik dengan data besar
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 5000; // Tingkatkan untuk menangani puluhan ribu baris
    }
}