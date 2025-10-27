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
            'TMT JABATAN',
            'JOB GRADE',
            'PERSON GRADE',
            'LOKASI KERJA',
            'AWAL LOKASI KERJA',
            'STATUS',
            'JABATAN STATUS',
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
            'JURUSAN',
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
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row - update range to include all columns (A to BI = 59 columns)
        $sheet->getStyle('A1:BG1')->applyFromArray([
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
        $sheet->getStyle('A2:BG3')->applyFromArray([
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
            'J' => 15,  // TMT JABATAN
            'K' => 12,  // JOB GRADE
            'L' => 12,  // PERSON GRADE
            'M' => 35,  // LOKASI KERJA
            'N' => 35,  // AWAL LOKASI KERJA
            'O' => 20,  // STATUS
            'P' => 20,  // JABATAN STATUS
            'Q' => 18,  // SUB STATUS
            'R' => 30,  // ASAL INSTANSI
            'S' => 30,  // INSTANSI
            'T' => 15,  // JENIS KELAMIN
            'U' => 15,  // TANGGAL LAHIR
            'V' => 10,  // USIA
            'W' => 15,  // RENCANA MPP
            'X' => 15,  // RENCANA PENSIUN
            'Y' => 20,  // PENDIDIKAN DIAKUI
            'Z' => 20,  // PENDIDIKAN DIMILIKI
            'AA' => 15, // TMT KARYAWAN
            'AB' => 15, // MASA KERJA
            'AC' => 15, // TMT KJ TERTINGGI
            'AD' => 18, // MASA KJ TERTINGGI (TAHUN)
            'AE' => 25, // SUB KELUARGA JABATAN
            'AF' => 25, // KELUARGA JABATAN
            'AG' => 25, // FUNGSI JABATAN
            'AH' => 20, // JALUR KARIR
            'AI' => 20, // JENJANG KARIR
            'AJ' => 28, // KELOMPOK KELAS JABATAN
            'AK' => 25, // FUNGSI PEKERJAAN
            'AL' => 30, // LISENCE DIMILIKI
            'AM' => 12, // RATING
            'AN' => 18, // NO STKP
            'AO' => 15, // MASA BERLAKU
            'AP' => 25, // LISENCE DIBAYARKAN JANUARI
            'AQ' => 20, // JURUSAN
            'AR' => 12, // AGAMA
            'AS' => 15, // NILAI NPI 2022
            'AT' => 15, // KATEGORI
            'AU' => 18, // NO KTP
            'AV' => 40, // ALAMAT KTP
            'AW' => 18, // NO KONTRAK
            'AX' => 30, // EMAIL
            'AY' => 15, // NO HP
            'AZ' => 18, // STATUS PERNIKAHAN
            'BA' => 15, // GENERASI
            'BB' => 25, // NO SK JABATAN TERAKHIR
            'BC' => 18, // TGL SK JABATAN TERAKHIR
            'BD' => 20, // CEK LISENCE/SERKOM
            'BE' => 12, // KPI 2023
            'BF' => 15, // KRITERIA
            'BG' => 20, // FUNGSI KONTRAK OS
        ];
    }
}