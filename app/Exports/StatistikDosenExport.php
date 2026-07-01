<?php

namespace App\Exports;

use App\Models\Dosen;
use App\Filament\Resources\StatistikDosen\StatistikDosenResource;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StatistikDosenExport implements FromQuery, WithMapping, WithEvents, ShouldAutoSize, WithCustomStartCell
{
    protected $jenisPeriode;
    protected $bulan;
    protected $minggu;
    protected $tahun;
    private $rowNumber = 0;

    public function __construct($jenisPeriode, $bulan, $minggu, $tahun)
    {
        $this->jenisPeriode = $jenisPeriode;
        $this->bulan = $bulan;
        $this->minggu = $minggu;
        $this->tahun = $tahun;
    }

    public function startCell(): string
    {
        return 'A9';
    }

    public function query()
    {
        $query = Dosen::query();
        $query = $query->withCount(StatistikDosenResource::getWithCountMap(
            $this->jenisPeriode,
            $this->bulan,
            $this->minggu,
            $this->tahun
        ));
        
        // Fallback sorting for PostgreSQL (cannot sum SELECT aliases in ORDER BY)
        $query->orderBy('ketua_kumulatif_count', 'desc')
              ->orderBy('p1_kumulatif_count', 'desc')
              ->orderBy('p2_kumulatif_count', 'desc');

        return $query;
    }

    public function map($dosen): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $dosen->nama,
            $dosen->pembimbing_periode_count ?? 0,
            $dosen->ketua_periode_count ?? 0,
            $dosen->pembimbing_kumulatif_count ?? 0,
            $dosen->ketua_kumulatif_count ?? 0,
            ($dosen->p1_periode_count ?? 0) + ($dosen->p2_periode_count ?? 0),
            ($dosen->p1_kumulatif_count ?? 0) + ($dosen->p2_kumulatif_count ?? 0),
            ($dosen->ketua_kumulatif_count ?? 0) + ($dosen->p1_kumulatif_count ?? 0) + ($dosen->p2_kumulatif_count ?? 0),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Get dynamic period text
                $teksPeriode = $this->getTeksPeriode();
                $teksTahun = $this->tahun !== 'semua' ? $this->tahun : '';
                $teksKumulatif = trim("Kumulatif TA $teksTahun");
                
                // Set fixed headers
                $sheet->setCellValue('A1', 'REKAPITULASI DOSEN PEMBIMBING DAN PENGUJI TUGAS AKHIR I');
                $sheet->setCellValue('A2', 'PROGRAM STUDI TEKNIK INDUSTRI FAKULTAS TEKNOLOGI INDUSTRI');
                $sheet->setCellValue('A4', $teksPeriode);
                
                // Row 5 headers
                $sheet->setCellValue('A5', 'No.');
                $sheet->setCellValue('B5', 'Nama Dosen');
                $sheet->setCellValue('C5', 'Dosen Pembimbing dan Ketua Sidang');
                $sheet->setCellValue('G5', 'Dosen Penguji');
                $sheet->setCellValue('I5', 'Kumulatif Ketua Sidang & Penguji');

                // Row 6 headers
                $sheet->setCellValue('C6', $teksPeriode);
                $sheet->setCellValue('E6', $teksKumulatif);
                $sheet->setCellValue('G6', $teksPeriode);
                $sheet->setCellValue('H6', $teksKumulatif);

                // Row 7 headers
                $sheet->setCellValue('C7', 'Pembimbing');
                $sheet->setCellValue('D7', 'Ketua Sidang');
                $sheet->setCellValue('E7', 'Pembimbing');
                $sheet->setCellValue('F7', 'Ketua Sidang');
                $sheet->setCellValue('G7', 'Penguji');
                $sheet->setCellValue('H7', 'Kumulatif');

                // Merge cells for title and period
                $sheet->mergeCells('A1:I1');
                $sheet->mergeCells('A2:I2');
                $sheet->mergeCells('A4:I4');

                $sheet->mergeCells('A5:A7');
                $sheet->mergeCells('B5:B7');
                $sheet->mergeCells('C5:F5');
                $sheet->mergeCells('G5:H5');
                $sheet->mergeCells('I5:I7');
                
                $sheet->mergeCells('C6:D6');
                $sheet->mergeCells('E6:F6');

                // Styling
                $lastRow = 8 + $this->rowNumber; // Data starts at 9
                
                // Title and Period Styles
                $sheet->getStyle('A1:A4')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Table Header Styles
                $sheet->getStyle('A5:I7')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE2EFDA'], // Light green similar to Excel default
                    ],
                ]);

                // Data borders
                $sheet->getStyle('A8:I' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                
                // Center align specific columns
                $sheet->getStyle('A8:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C8:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Set Column Widths
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('I')->setWidth(15);
            },
        ];
    }

    private function getTeksPeriode()
    {
        if ($this->bulan === 'semua' && $this->tahun === 'semua') {
            return 'Status Periode Keseluruhan';
        }
        
        $namaBulan = '';
        if ($this->bulan !== 'semua') {
            $bulanArray = [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ];
            $namaBulan = $bulanArray[$this->bulan] ?? '';
        }
        
        $teksTahun = $this->tahun !== 'semua' ? $this->tahun : '';
        $teksMinggu = ($this->jenisPeriode === 'mingguan' && $this->minggu !== 'semua') ? "M{$this->minggu} " : '';
        
        return trim("Status Periode $teksMinggu$namaBulan $teksTahun");
    }
}
