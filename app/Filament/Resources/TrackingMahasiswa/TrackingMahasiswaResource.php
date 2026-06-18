<?php

namespace App\Filament\Resources\TrackingMahasiswa;

use App\Filament\Resources\TrackingMahasiswa\Pages\ManageTrackingMahasiswa;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Sidang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrackingMahasiswaResource extends Resource
{
    protected static ?string $model = Mahasiswa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Tracking Mahasiswa';
    protected static ?int $navigationSort = 8;

    protected static ?string $slug = 'tracking-mahasiswa';

    protected static ?string $modelLabel = 'Tracking Mahasiswa';
    protected static ?string $pluralModelLabel = 'Tracking Mahasiswa';

    protected static ?string $recordTitleAttribute = 'nama';

    /**
     * Admin dan Dosen bisa akses halaman ini.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->isAdmin() || $user?->isDosen();
    }

    /**
     * Read-only: tidak bisa create/edit/delete dari sini.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    /**
     * Helper: Tentukan progress sidang mahasiswa.
     */
    private static function getProgress(int $mahasiswaId): string
    {
        $hasTa2Lulus = Sidang::where('mahasiswa_id', $mahasiswaId)
            ->where('jenis_sidang', 'Sidang TA 2')
            ->where('status', 'lulus')
            ->exists();

        if ($hasTa2Lulus) {
            return 'Sudah TA 2';
        }

        $hasTa1Lulus = Sidang::where('mahasiswa_id', $mahasiswaId)
            ->where('jenis_sidang', 'Seminar TA 1')
            ->where('status', 'lulus')
            ->exists();

        if ($hasTa1Lulus) {
            return 'Sudah TA 1';
        }

        return 'Belum Sidang';
    }

    /**
     * Helper: Cari dosen yang sedang login berdasarkan email.
     */
    private static function getLoggedInDosen(): ?Dosen
    {
        $user = auth()->user();
        if (!$user || !$user->isDosen()) {
            return null;
        }
        return Dosen::where('email', $user->email)->first();
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isAdmin = $user?->isAdmin() ?? false;

        $columns = [
            TextColumn::make('nama')
                ->label('Nama Mahasiswa')
                ->searchable()
                ->sortable(),

            TextColumn::make('nim')
                ->label('NIM')
                ->searchable()
                ->sortable(),

            TextColumn::make('judul_ta')
                ->label('Judul TA')
                ->limit(50)
                ->tooltip(fn ($record) => $record->judul_ta)
                ->wrap(),

            TextColumn::make('no_hp')
                ->label('No. HP')
                ->searchable(),

            TextColumn::make('pembimbing.nama')
                ->label('Pembimbing')
                ->sortable()
                ->searchable(),
        ];

        // Kolom Progress
        $columns[] = TextColumn::make('progress')
            ->label('Progress')
            ->badge()
            ->getStateUsing(fn ($record) => self::getProgress($record->id))
            ->color(fn (string $state): string => match ($state) {
                'Belum Sidang' => 'danger',
                'Sudah TA 1' => 'warning',
                'Sudah TA 2' => 'success',
                default => 'gray',
            })
            ->icon(fn (string $state): string => match ($state) {
                'Belum Sidang' => 'heroicon-m-x-circle',
                'Sudah TA 1' => 'heroicon-m-clock',
                'Sudah TA 2' => 'heroicon-m-check-circle',
                default => 'heroicon-m-question-mark-circle',
            });

        return $table
            ->columns($columns)
            ->modifyQueryUsing(function (Builder $query) use ($isAdmin) {
                $progressSql = "
                    CASE
                        WHEN EXISTS (SELECT 1 FROM sidang WHERE sidang.mahasiswa_id = mahasiswa.id AND jenis_sidang = 'Sidang TA 2' AND status = 'lulus') THEN 'Sudah TA 2'
                        WHEN EXISTS (SELECT 1 FROM sidang WHERE sidang.mahasiswa_id = mahasiswa.id AND jenis_sidang = 'Seminar TA 1' AND status = 'lulus') THEN 'Sudah TA 1'
                        ELSE 'Belum Sidang'
                    END
                ";
                
                $query->selectRaw("mahasiswa.*, ($progressSql) as progress_group");

                // Dosen: hanya lihat mahasiswa bimbingannya
                if (!$isAdmin) {
                    $dosen = self::getLoggedInDosen();
                    if ($dosen) {
                        $query->where('pembimbing_id', $dosen->id);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                }
                return $query;
            })
            ->filters([
                SelectFilter::make('rumpun_ta')
                    ->label('Rumpun TA')
                    ->options([
                        'SPOI' => 'SPOI',
                        'PSIE' => 'PSIE',
                        'KPDP' => 'KPDP',
                        'RPTE' => 'RPTE',
                    ]),
                SelectFilter::make('progress')
                    ->label('Progress')
                    ->options([
                        'belum' => 'Belum Sidang',
                        'ta1' => 'Sudah TA 1',
                        'ta2' => 'Sudah TA 2',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        if (!$value) return $query;

                        if ($value === 'ta2') {
                            // Punya Sidang TA 2 lulus
                            return $query->whereHas('sidangTa2Lulus');
                        } elseif ($value === 'ta1') {
                            // Punya TA 1 lulus tapi belum TA 2 lulus
                            return $query->whereHas('sidangTa1Lulus')
                                ->whereDoesntHave('sidangTa2Lulus');
                        } else {
                            // Belum ada TA 1 lulus
                            return $query->whereDoesntHave('sidangTa1Lulus');
                        }
                    }),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->paginated([10, 25, 50, 100])
            ->defaultSort('nama');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTrackingMahasiswa::route('/'),
        ];
    }
}
