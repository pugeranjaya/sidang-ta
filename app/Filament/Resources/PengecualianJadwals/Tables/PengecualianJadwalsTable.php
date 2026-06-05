<?php

namespace App\Filament\Resources\PengecualianJadwals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PengecualianJadwalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('jam_mulai_diblokir')
                    ->label('Mulai Blokir')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('jam_selesai_diblokir')
                    ->label('Selesai Blokir')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('keterangan')
                    ->label('Keterangan / Alasan')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
