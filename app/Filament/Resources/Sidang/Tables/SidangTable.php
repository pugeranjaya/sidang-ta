<?php

namespace App\Filament\Resources\Sidang\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SidangTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Belum ada data Sidang')
            ->emptyStateDescription('Silakan tambahkan data jadwal sidang baru melalui tombol di atas.')
            ->columns([
                TextColumn::make('mahasiswa.nama')
                    ->label('Mahasiswa')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: left !important; justify-content: flex-start !important;',
                    ])
                    ->description(fn($record) => $record->mahasiswa?->nim)
                    ->searchable(['nama', 'nim'])
                    ->sortable(),
                TextColumn::make('jenis_sidang')
                    ->label('Jenis Sidang')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: center !important; justify-content: center !important;',
                    ])
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Seminar TA 1' => 'info',
                        'Seminar TA 2' => 'success',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('tanggal')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: center !important; justify-content: center !important;',
                    ])
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('jam_mulai')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: center !important; justify-content: center !important;',
                    ])
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('jam_selesai')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: center !important; justify-content: center !important;',
                    ])
                    ->time('H:i')
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('ruangan')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: left !important; justify-content: flex-start !important;',
                    ])
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('status')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'justify-content: flex-start !important;',
                    ])
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'belum lulus' => 'danger',
                        'lulus' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'belum lulus' => 'heroicon-o-clock',
                        'lulus' => 'heroicon-o-academic-cap',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                TextColumn::make('mahasiswa.pembimbing.nama')
                    ->label('Pembimbing')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: left !important; justify-content: flex-start !important;',
                    ])
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('ketuaSidang.nama')
                    ->label('Ketua Sidang')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: left !important; justify-content: flex-start !important;',
                    ])
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('md'),
                TextColumn::make('penguji1.nama')
                    ->label('Penguji 1')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: left !important; justify-content: flex-start !important;',
                    ])
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('md'),
                TextColumn::make('penguji2.nama')
                    ->label('Penguji 2')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: left !important; justify-content: flex-start !important;',
                    ])
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('md'),
                TextColumn::make('penguji3.nama')
                    ->label('Penguji 3')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: left !important; justify-content: flex-start !important;',
                    ])
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('penguji4.nama')
                    ->label('Penguji 4')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: left !important; justify-content: flex-start !important;',
                    ])
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn() => auth()->user()?->isAdmin()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()?->isAdmin()),
                    BulkAction::make('tandaiBelumLulus')
                        ->label('Tandai Belum Lulus')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'belum lulus']);
                            });
                        })
                        ->visible(fn() => auth()->user()?->isAdmin())
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('tandaiLulus')
                        ->label('Tandai Lulus')
                        ->icon('heroicon-o-academic-cap')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'lulus']);
                            });
                        })
                        ->visible(fn() => auth()->user()?->isAdmin())
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])->paginated([10, 50, 70, 100, 150, 200]);
    }
}
