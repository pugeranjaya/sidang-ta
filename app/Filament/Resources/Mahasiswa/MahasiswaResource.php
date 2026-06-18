<?php

namespace App\Filament\Resources\Mahasiswa;

use App\Filament\Resources\Mahasiswa\Pages\CreateMahasiswa;
use App\Filament\Resources\Mahasiswa\Pages\EditMahasiswa;
use App\Filament\Resources\Mahasiswa\Pages\ListMahasiswa;
use App\Models\Mahasiswa;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

class MahasiswaResource extends Resource
{
    protected static ?string $model = Mahasiswa::class;

    protected static ?string $pluralModelLabel = 'Mahasiswa';
    protected static ?string $modelLabel = 'Mahasiswa';
    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'mahasiswa';

    protected static ?string $recordTitleAttribute = 'nim';

    public static function getNavigationIcon(): ?string
    {
        return 'icon-mahasiswa';
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
        return ['nama', 'nim'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('nama')
                    ->label('Nama Mahasiswa')
                    ->required()
                    ->maxLength(255),

                TextInput::make('nim')
                    ->label('NIM')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->placeholder('Contoh: 2110012345'),

                TextInput::make('no_hp')
                    ->label('No. HP / WA')
                    ->tel()
                    ->maxLength(20)
                    ->placeholder('Contoh: 081234567890'),

                TextInput::make('judul_ta')
                    ->label('Judul TA')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Perancangan Sistem Informasi ...'),

                Select::make('pembimbing_id')
                    ->label('Pembimbing')
                    ->relationship('pembimbing', 'nama')
                    ->searchable()
                    ->preload()
                    ->reactive()  // ← trigger auto-fill
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $dosen = \App\Models\Dosen::find($state);
                            if ($dosen) {
                                $rumpun = is_array($dosen->rumpun_keahlian)
                                    ? implode(', ', $dosen->rumpun_keahlian)
                                    : $dosen->rumpun_keahlian ?? '';
                                $set('rumpun_ta', $rumpun);
                            }
                        } else {
                            $set('rumpun_ta', '');
                        }
                    })
                    ->nullable(),

                TextInput::make('rumpun_ta')
                    ->label('Rumpun TA')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Otomatis dari dosen pembimbing')
                    ->disabled()  // read-only
                    ->helperText('Rumpun TA mengikuti rumpun keahlian dosen pembimbing')
                    ->dehydrated(true),  // tetap disimpan meski disabled
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
                    ->searchable(),

                TextColumn::make('nim')
                    ->label('NIM')
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('judul_ta')
                    ->label('Judul TA')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: left !important; justify-content: flex-start !important;',
                    ])
                    ->limit(50),

                TextColumn::make('rumpun_ta')
                    ->label('Rumpun TA')
                    ->alignCenter(),

                TextColumn::make('pembimbing.nama')
                    ->label('Pembimbing')
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'text-align: left !important; justify-content: flex-start !important;',
                    ])
                    ->searchable(),
            ])
            ->filters([
                // tambah filter kalau perlu
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 50, 70, 100, 150, 200]);

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
            'index' => ListMahasiswa::route('/'),
            'create' => CreateMahasiswa::route('/create'),
            'edit' => EditMahasiswa::route('/{record}/edit'),
        ];
    }
}