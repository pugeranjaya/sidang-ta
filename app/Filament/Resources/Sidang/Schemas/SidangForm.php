<?php

namespace App\Filament\Resources\Sidang\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Get;

class SidangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('mahasiswa_id')
                    ->label('Mahasiswa')
                    ->relationship('mahasiswa', 'nama')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nama} ({$record->nim})")
                    ->searchable(['nama', 'nim'])
                    ->preload()
                    ->required(),
                Select::make('jenis_sidang')
                    ->label('Jenis Sidang')
                    ->options([
                        'Seminar TA 1' => 'Seminar TA 1',
                        'Seminar TA 2' => 'Seminar TA 2',
                    ])
                    ->default('Seminar TA 1')
                    ->live()
                    ->required(),
                DatePicker::make('tanggal')
                    ->required(),
                TimePicker::make('jam_mulai')
                    ->required(),
                TimePicker::make('jam_selesai')
                    ->required(),
                Select::make('ruangan')
                    ->label('Ruangan')
                    ->options([
                        'Ruang Sidang Jurusan Teknik Industri' => 'Ruang Sidang Jurusan Teknik Industri',
                        'Ruang TUK Jurusan Teknik Industri' => 'Ruang TUK Jurusan Teknik Industri',
                        'Ruang Seminar LT-2 Jurusan Teknik Industri' => 'Ruang Seminar LT-2 Jurusan Teknik Industri',
                    ])
                    ->required(),
                Select::make('status')
                    ->options(['belum lulus' => 'Belum Lulus', 'lulus' => 'Lulus'])
                    ->default('belum lulus')
                    ->required(),
                Select::make('ketua_sidang_id')
                    ->label('Ketua Sidang')
                    ->relationship('ketuaSidang', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('penguji1_id')
                    ->label('Penguji 1')
                    ->relationship('penguji1', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('penguji2_id')
                    ->label('Penguji 2')
                    ->relationship('penguji2', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('penguji3_id')
                    ->label('Penguji 3')
                    ->relationship('penguji3', 'nama')
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => $get('jenis_sidang') === 'Seminar TA 2')
                    ->required(fn($get) => $get('jenis_sidang') === 'Seminar TA 2'),
                Select::make('penguji4_id')
                    ->label('Penguji 4')
                    ->relationship('penguji4', 'nama')
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => $get('jenis_sidang') === 'Seminar TA 2')
                    ->required(fn($get) => $get('jenis_sidang') === 'Seminar TA 2'),
            ]);
    }
}
