<?php

namespace App\Filament\Resources\StatistikDosen\Pages;

use App\Filament\Resources\StatistikDosen\StatistikDosenResource;
use Filament\Resources\Pages\ManageRecords;

class ManageStatistikDosen extends ManageRecords
{
    protected static string $resource = StatistikDosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for statistics
        ];
    }

    public function getTabs(): array
    {
        $months = \App\Models\Sidang::selectRaw('DISTINCT DATE_FORMAT(tanggal, "%Y-%m") as bulan')
            ->whereNotNull('tanggal')
            ->orderByDesc('bulan')
            ->pluck('bulan')
            ->toArray();
            
        $tabs = [
            'semua' => \Filament\Schemas\Components\Tabs\Tab::make('Total Semua Bulan')
                ->modifyQueryUsing(fn ($query) => $query->withCount(StatistikDosenResource::getWithCountMap(null)))
        ];
        
        foreach ($months as $bulan) {
            $monthLabel = \Illuminate\Support\Carbon::parse($bulan . '-01')->translatedFormat('F Y'); // misal: Mei 2026
            $tabs[$bulan] = \Filament\Schemas\Components\Tabs\Tab::make($monthLabel)
                ->modifyQueryUsing(fn ($query) => $query->withCount(StatistikDosenResource::getWithCountMap($bulan)));
        }
        
        return $tabs;
    }
}
