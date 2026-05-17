<?php

namespace App\Filament\Resources\JadwalMengajar\Schemas;
use App\Models\Dosen;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class JadwalMengajarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('dosen_id')
                ->label('Nama Dosen')
                ->relationship('dosen', 'nama')
                ->searchable()
                ->preload()
                ->required()
                ->placeholder('Pilih nama dosen...'),

            Select::make('hari')
                ->label('Hari')
                ->options([
                    'Senin' => 'Senin',
                    'Selasa' => 'Selasa',
                    'Rabu' => 'Rabu',
                    'Kamis' => 'Kamis',
                    'Jumat' => 'Jumat',
                    'Sabtu' => 'Sabtu',
                ])
                ->required()
                ->placeholder('Pilih hari...'),

            TimePicker::make('jam_mulai')
                ->label('Jam Mulai')
                ->required()
                ->seconds(false)
                ->placeholder('Pilih jam mulai'),

            TimePicker::make('jam_selesai')
                ->label('Jam Selesai')
                ->required()
                ->seconds(false)
                ->placeholder('Pilih jam selesai'),

            Select::make('ruangan')
                ->label('Ruangan')
                ->options([
                    'Patt.I-1B' => 'Patt.I-1B',
                    'Patt.I-1C' => 'Patt.I-1C',
                    'Patt.I-1D' => 'Patt.I-1D',
                    'Patt.II-1A' => 'Patt.II-1A',
                    'Patt.II-1B' => 'Patt.II-1B',
                    'Patt.II-1C' => 'Patt.II-1C',
                    'Patt.II-1D' => 'Patt.II-1D',
                    'Patt.II-2A' => 'Patt.II-2A',
                    'Patt.II-2B' => 'Patt.II-2B',
                    'Patt.II-2C' => 'Patt.II-2C',
                    'Patt.II-2D' => 'Patt.II-2D',
                ])
                ->searchable()
                ->placeholder('Pilih ruangan...'),

            TextInput::make('mata_kuliah')
                ->label('Mata Kuliah')
                ->required()
                ->maxLength(100)
                ->placeholder('Contoh: Pemrograman Komputer, Proses Manufaktur'),

            TextInput::make('tahun_akademik')
                ->label('Tahun Akademik')
                ->required()
                ->maxLength(20)
                ->placeholder('Contoh: 2025/2026')
                ->helperText('Format: Tahun mulai / Tahun akhir'),
        ]);
    }
}