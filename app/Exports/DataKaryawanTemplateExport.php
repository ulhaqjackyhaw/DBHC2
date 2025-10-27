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
            'Nama',
            'GENDER',
            'KODE JABATAN',
            'LOKASI',
            'UNIT',
            'JABATAN',
            'KELOMPOK KELAS JABATAN',
            'GRADE',
            'STATUS KEPEGAWAIAN',
            'ASAL INSTANSI',
            'TANGGAL LAHIR',
            'PENDIDIKAN TERAKHIR',
            'TMT',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:N1')->applyFromArray([
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
        $sheet->getStyle('A2:N3')->applyFromArray([
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
            'B' => 30,  // Nama
            'C' => 12,  // Gender
            'D' => 25,  // Kode Jabatan
            'E' => 35,  // Lokasi
            'F' => 35,  // Unit
            'G' => 40,  // Jabatan
            'H' => 28,  // Kelompok Kelas Jabatan
            'I' => 10,  // Grade
            'J' => 22,  // Status Kepegawaian
            'K' => 30,  // Asal Instansi
            'L' => 15,  // Tanggal Lahir
            'M' => 22,  // Pendidikan Terakhir
            'N' => 15,  // TMT
        ];
    }
}