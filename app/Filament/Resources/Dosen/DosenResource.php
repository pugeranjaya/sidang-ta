<?php

namespace App\Filament\Resources\Dosen;

use App\Filament\Resources\Dosen\Pages\CreateDosen;
use App\Filament\Resources\Dosen\Pages\EditDosen;
use App\Filament\Resources\Dosen\Pages\ListDosen;
use App\Models\Dosen;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class DosenResource extends Resource
{
    protected static ?string $model = Dosen::class;

    protected static ?string $slug = 'dosen';
    protected static ?string $pluralModelLabel = 'Dosen';
    protected static ?string $modelLabel = 'Dosen';
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function getNavigationIcon(): ?string
    {
        return 'icon-dosen';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nama', 'nip'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('nama')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Dr. Budi Santoso, S.T., M.T.'),

                TextInput::make('nip')
                    ->label('NIP')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->placeholder('Contoh: 197001011234567890'),

                \Filament\Forms\Components\CheckboxList::make('rumpun_keahlian')
                    ->label('Rumpun Keahlian')
                    ->options([
                        'SPOI' => 'Rekayasa Sistem Produksi dan Optimasi Industri (SPOI)',
                        'PSIE' => 'Rekayasa Perancangan Sistem Industri dan Ergonomi (PSIE)',
                        'KPDP' => 'Rekayasa Kualitas Proses dan Desain Produk (KPDP)',
                        'RPTE' => 'Rekayasa Rantai Pasok dan Tekno Ekonomi (RPTE)',
                    ])
                    ->columns(1)
                    ->required()
                    ->helperText('Pilih rumpun keahlian dosen (bisa lebih dari satu)')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: left !important; justify-content: flex-start !important;',
                    ])
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nip')
                    ->label('NIP')
                    ->alignCenter()
                    ->searchable(),


                TextColumn::make('rumpun_keahlian')
                    ->label('Rumpun Keahlian')
                    ->alignCenter()
                    ->badge()
                    ->separator(','),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDosen::route('/'),
            'create' => CreateDosen::route('/create'),
            'edit' => EditDosen::route('/{record}/edit'),
        ];
    }
}