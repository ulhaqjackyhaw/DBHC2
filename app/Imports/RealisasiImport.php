<?php

namespace App\Imports;

use App\Models\Realisasi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;

class RealisasiImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsEmptyRows, WithValidation
{
    use Importable;

    protected $tahun;

    public function __construct($tahun = null)
    {
        $this->tahun = $tahun ?? date('Y');
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Cek apakah program kerja sudah ada untuk tahun tertentu
        $existing = Realisasi::where('program_kerja', $row['program_kerja'] ?? $row['PROGRAM KERJA'])
            ->where('tahun', $this->tahun)
            ->first();

        if ($existing) {
            // Update data yang sudah ada
            $existing->update([
                'rkap' => $this->parseNumber($row['rkap'] ?? $row['RKAP'] ?? 0),
                'realisasi_jan_mar' => $this->parseNumber($row['realisasi_jan_mar'] ?? $row['REALISASI JAN-MAR'] ?? 0),
                'realisasi_jan_jun' => $this->parseNumber($row['realisasi_jan_jun'] ?? $row['REALISASI JAN-JUN'] ?? 0),
                'realisasi_jul_sep' => $this->parseNumber($row['realisasi_jul_sep'] ?? $row['REALISASI JUL-SEP'] ?? 0),
                'realisasi_jul_des' => $this->parseNumber($row['realisasi_jul_des'] ?? $row['REALISASI JUL-DES'] ?? 0),
            ]);
            return null;
        }

        // Buat data baru
        return new Realisasi([
            'program_kerja' => $row['program_kerja'] ?? $row['PROGRAM KERJA'],
            'tahun' => $this->tahun,
            'rkap' => $this->parseNumber($row['rkap'] ?? $row['RKAP'] ?? 0),
            'realisasi_jan_mar' => $this->parseNumber($row['realisasi_jan_mar'] ?? $row['REALISASI JAN-MAR'] ?? 0),
            'realisasi_jan_jun' => $this->parseNumber($row['realisasi_jan_jun'] ?? $row['REALISASI JAN-JUN'] ?? 0),
            'realisasi_jul_sep' => $this->parseNumber($row['realisasi_jul_sep'] ?? $row['REALISASI JUL-SEP'] ?? 0),
            'realisasi_jul_des' => $this->parseNumber($row['realisasi_jul_des'] ?? $row['REALISASI JUL-DES'] ?? 0),
        ]);
    }

    /**
     * Parse number from string (handle comma and dot separators)
     */
    private function parseNumber($value)
    {
        if (empty($value)) {
            return 0;
        }

        // Remove thousand separators (dots or commas)
        $value = str_replace(['.', ','], ['', '.'], $value);

        return (float) $value;
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'program_kerja' => 'required|string',
            'rkap' => 'nullable|numeric',
            'realisasi_jan_mar' => 'nullable|numeric',
            'realisasi_jan_jun' => 'nullable|numeric',
            'realisasi_jul_sep' => 'nullable|numeric',
            'realisasi_jul_des' => 'nullable|numeric',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'program_kerja.required' => 'Program Kerja wajib diisi',
            'rkap.numeric' => 'RKAP harus berupa angka',
            'realisasi_jan_mar.numeric' => 'Realisasi Jan-Mar harus berupa angka',
            'realisasi_jan_jun.numeric' => 'Realisasi Jan-Jun harus berupa angka',
            'realisasi_jul_sep.numeric' => 'Realisasi Jul-Sep harus berupa angka',
            'realisasi_jul_des.numeric' => 'Realisasi Jul-Des harus berupa angka',
        ];
    }
}
