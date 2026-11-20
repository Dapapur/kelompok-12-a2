<?php

namespace App\Exports;

use App\Models\AsteroidObservation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Helper\Html;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AsteroidObservationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection(): Collection
    {
        return AsteroidObservation::with('user')->latest()->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'JPL Reference ID',
            'Nama Asteroid',
            'Tanggal Observasi',
            'Diameter (m)',
            'Jarak ke Bumi (km)',
            'Kecepatan (km/h)',
            'Status Ancaman',
            'Prioritas',
            'Operator Pencatat',
            'Catatan Analisis',
        ];
    }

    public function map($obs): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $obs->jpl_id ?: '-',
            $obs->name,
            $obs->observation_date->format('Y-m-d'),
            $obs->diameter_m,
            $obs->distance_km,
            $obs->velocity_kmh,
            $obs->is_hazardous ? 'HAZARDOUS' : 'SAFE',
            $obs->priority_level,
            $obs->user->name ?? 'Operator',
            $this->formatNotesForExcel($obs->analysis_notes),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Aktifkan Wrap Text pada kolom Catatan Analisis (kolom K) agar baris baru tampil rapi
        $sheet->getStyle('K')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A:K')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function formatNotesForExcel(?string $notes)
    {
        if (empty(trim(strip_tags((string) $notes)))) {
            return '-';
        }

        // Bersihkan &nbsp; dan ubah tag list/kutipan agar terbaca rapi sebagai baris & poin di Excel
        $preparedHtml = str_replace('&nbsp;', ' ', $notes);
        $preparedHtml = str_ireplace(
            ['<ul>', '</ul>', '<ol>', '</ol>', '<li>', '</li>', '<blockquote>', '</blockquote>'],
            ['', '', '', '', '• ', '<br>', '<i>"', '"</i><br>'],
            $preparedHtml
        );

        try {
            $htmlHelper = new Html();
            return $htmlHelper->toRichTextObject($preparedHtml);
        } catch (\Throwable $e) {
            return trim(html_entity_decode(strip_tags($preparedHtml), ENT_QUOTES, 'UTF-8'));
        }
    }
}