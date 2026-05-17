<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class SidangActivityChart extends ChartWidget
{
    protected ?string $heading = 'Aktivitas Sidang';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $year = now()->year;
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $scheduledData = [];
        $finishedData = [];

        for ($m = 1; $m <= 12; $m++) {
            $scheduledData[] = \App\Models\Sidang::whereYear('tanggal', $year)
                ->whereMonth('tanggal', $m)
                ->count();

            $finishedData[] = \App\Models\Sidang::whereYear('tanggal', $year)
                ->whereMonth('tanggal', $m)
                ->where('status', 'selesai')
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Sidang (Terdaftar)',
                    'data' => $scheduledData,
                    'borderColor' => '#3b82f6',
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Sidang Selesai',
                    'data' => $finishedData,
                    'borderColor' => '#22c55e',
                    'fill' => true,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
