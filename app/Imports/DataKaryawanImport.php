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
        // Debug: Log header columns untuk troubleshooting (comment setelah selesai debug)
        \Log::info('Excel Row Keys: ' . json_encode(array_keys($row)));
        \Log::info('Excel Row Data: ' . json_encode($row));

        // Helper function to convert Excel dates
        $convertDate = function ($dateValue) {
            if (is_numeric($dateValue)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue)->format('d/m/Y');
            }
            return $dateValue;
        };

        // Helper function to get value with multiple possible keys
        $getValue = function ($row, ...$keys) {
            foreach ($keys as $key) {
                if (isset($row[$key]) && !empty($row[$key])) {
                    return $row[$key];
                }
            }
            return null;
        };

        return new DataKaryawan([
            // Mapping langsung 1:1 dengan header Excel
            'nik' => (string) $getValue($row, 'nik'),
            'nama' => $getValue($row, 'nama'),
            'kode_jabatan' => $getValue($row, 'kode_jabatan'),

            // Unit Structure
            'unit_deputy_egm' => $getValue($row, 'unit_deputy_egm'),
            'unit_assistant_deputy' => $getValue($row, 'unit_assistant_deputy'),
            'unit_division_head' => $getValue($row, 'unit_division_head'),
            'unit_department_head' => $getValue($row, 'unit_department_head'),
            'unit_kerja' => $getValue($row, 'unit_kerja'),

            // Job Information
            'jabatan' => $getValue($row, 'jabatan'),
            'tmt_jabatan' => $convertDate($getValue($row, 'tmt_jabatan')),
            'job_grade' => $getValue($row, 'job_grade'),
            'person_grade' => $getValue($row, 'person_grade'),
            'lokasi_kerja' => $getValue($row, 'lokasi_kerja') ?: $getValue($row, 'lokasi_kerja_awal'),  // Fallback ke awal jika kosong
            'awal_lokasi_kerja' => $getValue($row, 'awal_lokasi_kerja') ?: $getValue($row, 'lokasi_kerja'),
            'status' => $getValue($row, 'status') ?: $getValue($row, 'sub_status'),  // Fallback ke sub_status jika kolom status tidak ada
            'status_jabatan' => $getValue($row, 'status_jabatan'),
            'sub_status' => $getValue($row, 'sub_status'),
            'asal_instansi' => $getValue($row, 'asal_instansi'),
            'instansi' => $getValue($row, 'instansi'),

            // Personal Information
            'jenis_kelamin' => $getValue($row, 'jenis_kelamin'),
            'tanggal_lahir' => $convertDate($getValue($row, 'tanggal_lahir')),
            'usia' => $getValue($row, 'usia'),
            'rencana_mpp' => $getValue($row, 'rencana_mpp'),
            'rencana_pensiun' => $getValue($row, 'rencana_pensiun'),
            'pendidikan_diakui' => $getValue($row, 'pendidikan_diakui'),
            'pendidikan_dimiliki' => $getValue($row, 'pendidikan_dimiliki'),
            'tmt_karyawan' => $convertDate($getValue($row, 'tmt_karyawan')),
            'masa_kerja' => $getValue($row, 'masa_kerja'),
            'tmt' => $convertDate($getValue($row, 'tmt')),
            'tmt_kj_tertinggi' => $convertDate($getValue($row, 'tmt_kj_tertinggi')),
            'masa_kj_tertinggi_tahun' => $getValue($row, 'masa_kj_tertinggi_tahun'),

            // Job Classification
            'sub_keluarga_jabatan' => $getValue($row, 'sub_keluarga_jabatan'),
            'keluarga_jabatan' => $getValue($row, 'keluarga_jabatan'),
            'fungsi_jabatan' => $getValue($row, 'fungsi_jabatan'),
            'jalur_karir' => $getValue($row, 'jalur_karir'),
            'jenjang_karir' => $getValue($row, 'jenjang_karir'),
            'kelompok_kelas_jabatan' => $getValue($row, 'kelompok_kelas_jabatan'),
            'fungsi_pekerjaan' => $getValue($row, 'fungsi_pekerjaan'),
            'grade' => (string) $getValue($row, 'grade'),

            // License Information
            'lisence_dimiliki' => $getValue($row, 'lisence_dimiliki'),
            'rating' => $getValue($row, 'rating'),
            'no_stkp' => $getValue($row, 'no_stkp'),
            'masa_berlaku' => $convertDate($getValue($row, 'masa_berlaku')),
            'lisence_dibayarkan_januari' => $getValue($row, 'lisence_dibayarkan_januari'),

            // Additional Personal Data
            'jurusan' => $getValue($row, 'jurusan'),
            'agama' => $getValue($row, 'agama'),
            'nilai_npi_2022' => $getValue($row, 'nilai_npi_2022'),
            'kategori' => $getValue($row, 'kategori'),
            'no_ktp' => $getValue($row, 'no_ktp'),
            'alamat_ktp' => $getValue($row, 'alamat_ktp'),
            'no_kontrak' => $getValue($row, 'no_kontrak'),
            'email' => $getValue($row, 'email'),
            'no_hp' => $getValue($row, 'no_hp'),
            'status_pernikahan' => $getValue($row, 'status_pernikahan'),
            'generasi' => $getValue($row, 'generasi'),

            // Job History & Performance
            'no_sk_jabatan_terakhir' => $getValue($row, 'no_sk_jabatan_terakhir'),
            'tgl_sk_jabatan_terakhir' => $convertDate($getValue($row, 'tgl_sk_jabatan_terakhir')),
            'cek_lisence_serkom' => $getValue($row, 'cek_lisence_serkom'),
            'kpi_2023' => $getValue($row, 'kpi_2023'),
            'kriteria' => $getValue($row, 'kriteria'),
            'fungsi_kontrak_os' => $getValue($row, 'fungsi_kontrak_os'),
        ]);
    }



    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 50; // Reduced for 59 columns to avoid too many placeholders
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100; // Reduced for better memory management with extended columns
    }
}