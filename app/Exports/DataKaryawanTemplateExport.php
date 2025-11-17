<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DataKaryawanTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * @return array
     */
    public function array(): array
    {
        // Example rows for template
        return [
            [
                '18617368',
                'CONTOH : Andi Firmansyah D',
                'Laki-laki',
                'CGKD01AD03DIV06DEP022J0175',
                'KCU. Bandara Internasional Soekarno-Hatta',
                'T3 Ventilation & Air Conditioning Services',
                'T3 Ventilation & Air Conditioning Services Engineer',
                'BOD-4',
                '11',
                'Organic',
                'PT ANGKASA PURA INDONESIA',
                '23/07/1995',
                'S1',
                '23/12/2022'
            ],
            [
                '455425237',
                'CONTOH : Cantika Ayu',
                'Perempuan',
                'A2910',
                'KCU. Bandara Internasional Soekarno-Hatta',
                'Terminal 1 Building',
                'OS',
                'OS',
                '5',
                'Outsourcing',
                'PT KOSAMI SEJAHTERA UTAMA',
                '27/09/1993',
                'S1',
                '12/04/2022'
            ],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'NIK',
            'NAMA',
            'KODE JABATAN',
            'UNIT DEPUTY EGM',
            'UNIT ASSISTANT DEPUTY',
            'UNIT DIVISION HEAD',
            'UNIT DEPARTMENT HEAD',
            'UNIT KERJA',
            'JABATAN',
            'PERSON GRADE',
            'LOKASI KERJA',
            'AWAL LOKASI KERJA',
            'STATUS JABATAN',
            'SUB STATUS',
            'ASAL INSTANSI',
            'INSTANSI',
            'JENIS KELAMIN',
            'TANGGAL LAHIR',
            'USIA',
            'RENCANA MPP',
            'RENCANA PENSIUN',
            'PENDIDIKAN DIAKUI',
            'PENDIDIKAN DIMILIKI',
            'JURUSAN',
            'TMT KARYAWAN',
            'MASA KERJA',
            'TMT KJ TERTINGGI',
            'MASA KJ TERTINGGI (TAHUN)',
            'SUB KELUARGA JABATAN',
            'KELUARGA JABATAN',
            'FUNGSI JABATAN',
            'JALUR KARIR',
            'JENJANG KARIR',
            'KELOMPOK KELAS JABATAN',
            'FUNGSI PEKERJAAN',
            'LISENCE DIMILIKI',
            'RATING',
            'NO STKP',
            'MASA BERLAKU',
            'LISENCE DIBAYARKAN JANUARI',
            'AGAMA',
            'NILAI NPI 2022',
            'KATEGORI',
            'NO KTP',
            'ALAMAT KTP',
            'NO KONTRAK',
            'EMAIL',
            'NO HP',
            'STATUS PERNIKAHAN',
            'GENERASI',
            'NO SK JABATAN TERAKHIR',
            'TGL SK JABATAN TERAKHIR',
            'CEK LISENCE/SERKOM',
            'KPI 2023',
            'KRITERIA',
            'FUNGSI KONTRAK OS',
            'PENUGASAN',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row - update range to include all columns (A to BE = 57 columns)
        $sheet->getStyle('A1:BE1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style example rows
        $sheet->getStyle('A2:BE3')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEF3C7'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'italic' => true,
                'color' => ['rgb' => '92400E'],
            ],
        ]);

        // Center align specific columns
        $sheet->getStyle('A2:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C2:C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I2:I3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // NIK
            'B' => 30,  // NAMA
            'C' => 25,  // KODE JABATAN
            'D' => 25,  // UNIT DEPUTY EGM
            'E' => 25,  // UNIT ASSISTANT DEPUTY
            'F' => 25,  // UNIT DIVISION HEAD
            'G' => 25,  // UNIT DEPARTMENT HEAD
            'H' => 35,  // UNIT KERJA
            'I' => 40,  // JABATAN
            'J' => 12,  // PERSON GRADE
            'K' => 35,  // LOKASI KERJA
            'L' => 35,  // AWAL LOKASI KERJA
            'M' => 20,  // STATUS JABATAN
            'N' => 18,  // SUB STATUS
            'O' => 30,  // ASAL INSTANSI
            'P' => 30,  // INSTANSI
            'Q' => 15,  // JENIS KELAMIN
            'R' => 15,  // TANGGAL LAHIR
            'S' => 10,  // USIA
            'T' => 15,  // RENCANA MPP
            'U' => 15,  // RENCANA PENSIUN
            'V' => 20,  // PENDIDIKAN DIAKUI
            'W' => 20,  // PENDIDIKAN DIMILIKI
            'X' => 20,  // JURUSAN
            'Y' => 15,  // TMT KARYAWAN
            'Z' => 15,  // MASA KERJA
            'AA' => 15, // TMT KJ TERTINGGI
            'AB' => 18, // MASA KJ TERTINGGI (TAHUN)
            'AC' => 25, // SUB KELUARGA JABATAN
            'AD' => 25, // KELUARGA JABATAN
            'AE' => 25, // FUNGSI JABATAN
            'AF' => 20, // JALUR KARIR
            'AG' => 20, // JENJANG KARIR
            'AH' => 28, // KELOMPOK KELAS JABATAN
            'AI' => 25, // FUNGSI PEKERJAAN
            'AJ' => 30, // LISENCE DIMILIKI
            'AK' => 12, // RATING
            'AL' => 18, // NO STKP
            'AM' => 15, // MASA BERLAKU
            'AN' => 25, // LISENCE DIBAYARKAN JANUARI
            'AO' => 12, // AGAMA
            'AP' => 15, // NILAI NPI 2022
            'AQ' => 15, // KATEGORI
            'AR' => 18, // NO KTP
            'AS' => 40, // ALAMAT KTP
            'AT' => 18, // NO KONTRAK
            'AU' => 30, // EMAIL
            'AV' => 15, // NO HP
            'AW' => 18, // STATUS PERNIKAHAN
            'AX' => 15, // GENERASI
            'AY' => 25, // NO SK JABATAN TERAKHIR
            'AZ' => 18, // TGL SK JABATAN TERAKHIR
            'BA' => 20, // CEK LISENCE/SERKOM
            'BB' => 12, // KPI 2023
            'BC' => 15, // KRITERIA
            'BD' => 20, // FUNGSI KONTRAK OS
            'BE' => 20, // PENUGASAN
        ];
    }
}