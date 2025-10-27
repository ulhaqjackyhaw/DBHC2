<?php

namespace App\Imports;

use App\Models\DataPgs;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class DataPgsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsEmptyRows
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
        $tanggalPgs = $row['tanggal_pgs'] ?? $row['tanggal pgs'] ?? $row['TANGGAL PGS'] ?? $row['tanggal mulai pgs'] ?? $row['TANGGAL MULAI PGS'];
        if (is_numeric($tanggalPgs)) {
            // Convert Excel serial date to date string
            $tanggalPgs = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalPgs)->format('d/m/Y');
        } elseif (is_string($tanggalPgs)) {
            // Try to parse various date formats
            try {
                if (strpos($tanggalPgs, '/') !== false) {
                    // Already in d/m/Y format
                    $parsedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $tanggalPgs);
                } elseif (strpos($tanggalPgs, '-') !== false) {
                    // Assume Y-m-d format
                    $parsedDate = \Carbon\Carbon::createFromFormat('Y-m-d', $tanggalPgs);
                } else {
                    throw new \Exception('Unknown date format');
                }
                $tanggalPgs = $parsedDate->format('d/m/Y');
            } catch (\Exception $e) {
                // Keep original format if parsing fails
            }
        }

        // Handle tanggal selesai PGS
        $tanggalSelesaiPgs = $row['tanggal_selesai_pgs'] ?? $row['tanggal selesai pgs'] ?? $row['TANGGAL SELESAI PGS'] ?? null;
        if ($tanggalSelesaiPgs && is_numeric($tanggalSelesaiPgs)) {
            $tanggalSelesaiPgs = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalSelesaiPgs)->format('d/m/Y');
        } elseif ($tanggalSelesaiPgs && is_string($tanggalSelesaiPgs)) {
            try {
                if (strpos($tanggalSelesaiPgs, '/') !== false) {
                    $parsedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $tanggalSelesaiPgs);
                } elseif (strpos($tanggalSelesaiPgs, '-') !== false) {
                    $parsedDate = \Carbon\Carbon::createFromFormat('Y-m-d', $tanggalSelesaiPgs);
                } else {
                    throw new \Exception('Unknown date format');
                }
                $tanggalSelesaiPgs = $parsedDate->format('d/m/Y');
            } catch (\Exception $e) {
                $tanggalSelesaiPgs = null;
            }
        }

        return new DataPgs([
            'nik' => (string) ($row['nik'] ?? $row['NIK']),
            'nama' => $row['nama'] ?? $row['NAMA'],
            'jabatan_definitif' => $row['jabatan_definitif'] ?? $row['jabatan definitif'] ?? $row['JABATAN DEFINITIF'],
            'jabatan_pgs' => $row['jabatan_pgs'] ?? $row['jabatan pgs'] ?? $row['JABATAN PGS'],
            'lokasi_unit_kerja' => $row['lokasi_unit_kerja'] ?? $row['lokasi/unit_kerja'] ?? $row['lokasi unit kerja'] ?? $row['LOKASI/UNIT KERJA'],
            'tanggal_pgs' => $tanggalPgs,
            'tanggal_selesai_pgs' => $tanggalSelesaiPgs,
        ]);
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 200;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 200;
    }
}