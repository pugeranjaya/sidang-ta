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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
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
                    ->regex('/^[a-zA-Z\s.,\'-]+$/')
                    ->validationMessages([
                        'regex' => 'Nama hanya boleh berisi huruf, spasi, titik, koma, tanda kutip, dan strip.',
                    ])
                    ->extraInputAttributes(['oninput' => "this.value = this.value.replace(/[^a-zA-Z\\s.,\\'-]/g, '')"])
                    ->placeholder('Contoh: Dr. Budi Santoso, S.T., M.T.'),

                TextInput::make('nip')
                    ->label('NIP')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->regex('/^[0-9]+$/')
                    ->validationMessages([
                        'regex' => 'NIP hanya boleh berisi angka.',
                    ])
                    ->extraInputAttributes(['oninput' => "this.value = this.value.replace(/[^0-9]/g, '')"])
                    ->placeholder('Contoh: 197001011234567890'),

                Group::make([
                    CheckboxList::make('rumpun_keahlian')
                        ->label('Rumpun Keahlian')
                        ->options([
                            'SPOI' => 'Rekayasa Sistem Produksi dan Optimasi Industri (SPOI)',
                            'PSIE' => 'Rekayasa Perancangan Sistem Industri dan Ergonomi (PSIE)',
                            'KPDP' => 'Rekayasa Kualitas Proses dan Desain Produk (KPDP)',
                            'RPTE' => 'Rekayasa Rantai Pasok dan Tekno Ekonomi (RPTE)',
                        ])
                        ->columns(1)
                        ->required()
                        ->helperText('Pilih satu atau lebih rumpun keahlian dosen'),
                ]),

                Group::make([
                    Toggle::make('abaikan_bentrok')
                        ->label('Abaikan Bentrok Jadwal')
                        ->default(false)
                        ->helperText('Aktifkan agar jadwal mengajar dosen ini DIABAIKAN saat pengecekan bentrok. Berguna jika dosen bersedia hadir meski ada kelas di waktu yang sama.')
                        ->onColor('warning')
                        ->offColor('gray'),

                    Toggle::make('status')
                        ->label('Status Aktif Dosen')
                        ->default(true)
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                            if ($state) {
                                $set('alasan_nonaktif', null);
                            }
                        })
                        ->onColor('success')
                        ->offColor('danger'),

                    Textarea::make('alasan_nonaktif')
                        ->label('Alasan Nonaktif')
                        ->required(fn (Get $get) => !$get('status'))
                        ->visible(fn (Get $get) => !$get('status'))
                        ->placeholder('Contoh: Cuti akademik, tugas belajar S3, sakit, dll.')
                        ->rows(3)
                        ->maxLength(500),
                ]),
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

                \Filament\Tables\Columns\ViewColumn::make('status')
                    ->label('Status')
                    ->alignCenter()
                    ->view('filament.tables.columns.status-toggle'),

                \Filament\Tables\Columns\ToggleColumn::make('abaikan_bentrok')
                    ->label('Abaikan Bentrok')
                    ->alignCenter()
                    ->onColor('warning')
                    ->offColor('gray'),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('status')
                    ->label('Status Dosen')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->placeholder('Semua'),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('toggle_status')
                    ->label('')
                    ->icon('')
                    ->extraAttributes(['style' => 'display: none !important; width: 0; height: 0; padding: 0; margin: 0;'])
                    ->modalHeading(fn (Dosen $record) => $record->status ? 'Nonaktifkan Dosen: ' . $record->nama : null)
                    ->modalDescription(fn (Dosen $record) => $record->status ? 'Isi alasan mengapa dosen ini perlu dinonaktifkan.' : null)
                    ->modalSubmitActionLabel('Simpan')
                    ->form(fn (Dosen $record) => $record->status ? [
                        Textarea::make('alasan_nonaktif')
                            ->label('Alasan Nonaktif')
                            ->required()
                            ->placeholder('Contoh: Cuti akademik, tugas belajar S3, sakit, dll.')
                            ->rows(3)
                            ->maxLength(500),
                    ] : [])
                    ->requiresConfirmation(fn (Dosen $record) => !$record->status)
                    ->modalHeading(fn (Dosen $record) => !$record->status ? 'Aktifkan Dosen: ' . $record->nama : 'Nonaktifkan Dosen: ' . $record->nama)
                    ->action(function (Dosen $record, array $data) {
                        if ($record->status) {
                            // Nonaktifkan
                            $record->update([
                                'status' => false,
                                'alasan_nonaktif' => $data['alasan_nonaktif'],
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title('Dosen Dinonaktifkan')
                                ->body($record->nama . ' telah dinonaktifkan.')
                                ->warning()
                                ->send();
                        } else {
                            // Aktifkan kembali
                            $record->update([
                                'status' => true,
                                'alasan_nonaktif' => null,
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title('Dosen Diaktifkan')
                                ->body($record->nama . ' telah diaktifkan kembali.')
                                ->success()
                                ->send();
                        }
                    }),
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