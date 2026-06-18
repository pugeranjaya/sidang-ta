<?php

namespace App\Filament\Resources\Sidang\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SidangInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('mahasiswa.nama')
                    ->label('Mahasiswa'),
                TextEntry::make('mahasiswa.nim')
                    ->label('NIM'),
                TextEntry::make('mahasiswa.pembimbing.nama')
                    ->label('Pembimbing'),
                TextEntry::make('jenis_sidang')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Seminar TA 1' => 'info',
                        'Sidang TA 2' => 'success',
                        default => 'gray',
                    }),
                TextEntry::make('tanggal')
                    ->date(),
                TextEntry::make('jam_mulai')
                    ->time(),
                TextEntry::make('jam_selesai')
                    ->time(),
                TextEntry::make('ruangan'),

                TextEntry::make('ketuaSidang.nama')
                    ->label('Ketua Sidang'),
                TextEntry::make('penguji1.nama')
                    ->label('Penguji 1'),
                TextEntry::make('penguji2.nama')
                    ->label('Penguji 2'),
                TextEntry::make('penguji3.nama')
                    ->label('Penguji 3')
                    ->visible(fn($record) => $record?->jenis_sidang === 'Sidang TA 2'),
                TextEntry::make('penguji4.nama')
                    ->label('Penguji 4')
                    ->visible(fn($record) => $record?->jenis_sidang === 'Sidang TA 2'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
