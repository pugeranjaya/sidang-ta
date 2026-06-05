<?php

namespace App\Filament\Resources\PengecualianJadwals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
class PengecualianJadwalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Detail Pengecualian Waktu')
                    ->description('Tentukan tanggal dan rentang jam yang tidak boleh dijadwalkan oleh sistem otomatis.')
                    ->schema([
                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d F Y'),

                        TimePicker::make('jam_mulai_diblokir')
                            ->label('Jam Mulai Diblokir')
                            ->seconds(false)
                            ->required(),

                        TimePicker::make('jam_selesai_diblokir')
                            ->label('Jam Selesai Diblokir')
                            ->seconds(false)
                            ->required(),

                        TextInput::make('keterangan')
                            ->label('Keterangan / Alasan')
                            ->placeholder('Contoh: Rapat Dosen Jurusan Pagi')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
