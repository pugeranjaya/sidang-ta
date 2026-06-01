<?php

namespace App\Filament\Resources\JadwalMengajar\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class JadwalMengajarTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dosen.nama')
                    ->label('Dosen')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: left !important; justify-content: flex-start !important;',
                    ])
                    ->sortable()
                    ->searchable(),
                TextColumn::make('hari')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'justify-content: flex-start !important;',
                    ])
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Senin' => 'danger',
                        'Selasa' => 'warning',
                        'Rabu' => 'success',
                        'Kamis' => 'info',
                        'Jumat' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('jam')
                    ->label('Waktu')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'justify-content: flex-start !important;',
                    ])
                    ->getStateUsing(fn($record) => substr($record->jam_mulai, 0, 5) . ' - ' . substr($record->jam_selesai, 0, 5))
                    ->icon('heroicon-m-clock'),
                TextColumn::make('mata_kuliah')
                    ->label('Mata Kuliah')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: left !important; justify-content: flex-start !important;',
                    ])
                    ->limit(50)
                    ->wrap()
                    ->searchable(),

                ToggleColumn::make('abaikan_bentrok')
                    ->label('Abaikan Bentrok')
                    ->alignCenter()
                    ->onColor('warning')
                    ->offColor('gray')
                    ->tooltip('Aktifkan untuk kasus Tim Teaching — jadwal ini akan diabaikan saat pengecekan bentrok penjadwalan sidang')
                    ->visible(fn() => auth()->user()?->isAdmin()),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('hari')
                    ->options([
                        'Senin' => 'Senin',
                        'Selasa' => 'Selasa',
                        'Rabu' => 'Rabu',
                        'Kamis' => 'Kamis',
                        'Jumat' => 'Jumat',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('dosen_id')
                    ->label('Dosen')
                    ->relationship('dosen', 'nama')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\TernaryFilter::make('abaikan_bentrok')
                    ->label('Abaikan Bentrok')
                    ->trueLabel('Yang diabaikan')
                    ->falseLabel('Yang dihitung')
                    ->placeholder('Semua')
                    ->visible(fn() => auth()->user()?->isAdmin()),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn() => auth()->user()?->isAdmin()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->visible(fn() => auth()->user()?->isAdmin()),
            ])
            ->paginated([10, 50, 70, 100, 150, 200]);
    }
}
