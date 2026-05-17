<?php

namespace App\Filament\Resources\JadwalMengajar\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JadwalMengajarInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('dosen.nama')
                ->label('Dosen')
                ->weight('bold')
                ->size('lg'),
            TextEntry::make('hari')
                ->badge()
                ->color('success'),
            TextEntry::make('waktu')
                ->label('Waktu')
                ->getStateUsing(fn($record) => substr($record->jam_mulai, 0, 5) . ' - ' . substr($record->jam_selesai, 0, 5))
                ->icon('heroicon-m-clock'),
            TextEntry::make('mata_kuliah')
                ->label('Mata Kuliah'),
            TextEntry::make('ruangan')
                ->label('Ruangan')
                ->placeholder('-'),
            TextEntry::make('tahun_akademik')
                ->label('Tahun Akademik'),
        ]);
    }
}