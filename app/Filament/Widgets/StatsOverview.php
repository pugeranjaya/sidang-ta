<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Mahasiswa', \App\Models\Mahasiswa::count())
                ->description('Lihat data Mahasiswa')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->url(auth()->user()?->isAdmin() ? \App\Filament\Resources\Mahasiswa\MahasiswaResource::getUrl('index') : null),

            Stat::make('Total Dosen', \App\Models\Dosen::count())
                ->description('Lihat data Dosen')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning')
                ->chart([3, 5, 2, 8, 4, 9, 7])
                ->url(auth()->user()?->isAdmin() ? \App\Filament\Resources\Dosen\DosenResource::getUrl('index') : null),

            Stat::make('Sidang Bulan Ini', \App\Models\Sidang::whereMonth('tanggal', now()->month)->count())
                ->description('Jadwal Sidang Terdaftar')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success')
                ->url(\App\Filament\Resources\Sidang\SidangResource::getUrl('index')),

            Stat::make('Tingkat Kelulusan', function () {
                $totalSelesai = \App\Models\Sidang::where('status', 'selesai')->count();
                $totalMahasiswa = \App\Models\Mahasiswa::count();
                if ($totalMahasiswa === 0)
                    return '0%';

                $percentage = ($totalSelesai / $totalMahasiswa) * 100;
                return round($percentage, 1) . '%';
            })
                ->description('Berdasarkan mahasiswa yang lulus sidang')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),
        ];
    }
}
