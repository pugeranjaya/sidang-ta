<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class RumpunKeahlianChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Rumpun Dosen';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $dosenList = \App\Models\Dosen::all();

        $data = [
            'SPOI' => 0,
            'PSIE' => 0,
            'KPDP' => 0,
            'RPTE' => 0,
        ];

        foreach ($dosenList as $dosen) {
            $rumpuns = (array) $dosen->rumpun_keahlian;
            foreach ($rumpuns as $rumpun) {
                if (isset($data[$rumpun])) {
                    $data[$rumpun]++;
                }
            }
        }

        // Filter out zero values for a cleaner chart
        $data = array_filter($data, fn($count) => $count > 0);

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Dosen',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#3b82f6', // SPOI: blue
                        '#eab308', // PSIE: yellow
                        '#ef4444', // KPDP: red
                        '#22c55e', // RPTE: green
                    ],
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
