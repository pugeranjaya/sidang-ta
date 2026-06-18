<?php

namespace App\Filament\Resources\TrackingMahasiswa\Pages;

use App\Filament\Resources\TrackingMahasiswa\TrackingMahasiswaResource;
use Filament\Resources\Pages\ManageRecords;

class ManageTrackingMahasiswa extends ManageRecords
{
    protected static string $resource = TrackingMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Read-only, no create action
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => \Filament\Schemas\Components\Tabs\Tab::make('Semua'),
            'belum_sidang' => \Filament\Schemas\Components\Tabs\Tab::make('Belum Sidang')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->whereDoesntHave('sidangTa1Lulus')),
            'sudah_ta1' => \Filament\Schemas\Components\Tabs\Tab::make('Sudah TA 1')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->whereHas('sidangTa1Lulus')->whereDoesntHave('sidangTa2Lulus')),
            'sudah_ta2' => \Filament\Schemas\Components\Tabs\Tab::make('Sudah TA 2')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->whereHas('sidangTa2Lulus')),
        ];
    }
}
