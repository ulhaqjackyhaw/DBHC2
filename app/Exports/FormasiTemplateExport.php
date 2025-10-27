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

class FormasiTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * @return array
     */
    public function array(): array
    {
        // Return 2 example rows for template
        return [
            [
                'HLPDCONTOHDIV00AM004J1101',
                'KC Bandara Halim Perdanakusuma',
                'Airport Rescue & Fire Fighting',
                'Airport Rescue & Fire Fighting Chief',
                'BOD-4',
                '11',

            ],
            [
                'CONTOHREGD008AD00DIV58DEP000J1372',
                'Regional 1',
                'Accounting',
                'Regional Accounting Division Head',
                'BOD-2',
                '16',
            ],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'KODE JABATAN',
            'LOKASI',
            'UNIT',
            'JABATAN',
            'KELOMPOK KELAS JABATAN',
            'GRADE',
            'KUOTA',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '1F2937'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3B82F6'],
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
        $sheet->getStyle('A2:G3')->applyFromArray([
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
        $sheet->getStyle('E2:E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F2:F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G2:G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 40,  // KODE JABATAN
            'B' => 40,  // LOKASI
            'C' => 35,  // UNIT
            'D' => 40,  // JABATAN
            'E' => 30,  // KELOMPOK KELAS JABATAN
            'F' => 12,  // GRADE
            'G' => 12,  // KUOTA
        ];
    }
}