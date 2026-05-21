<?php

namespace App\Filament\Resources\StatistikDosen;

use App\Filament\Resources\StatistikDosen\Pages\ManageStatistikDosen;
use App\Models\Dosen;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StatistikDosenResource extends Resource
{
    protected static ?string $model = Dosen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Statistik Dosen';
    protected static ?int $navigationSort = 7;

    protected static ?string $slug = 'statistik-dosen';

    protected static ?string $modelLabel = 'Statistik Dosen';
    protected static ?string $pluralModelLabel = 'Statistik Dosen';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getWithCountMap(?string $bulan = null, ?string $tahun = null): array
    {
        $filterBukanPembimbing = function($q) use ($bulan, $tahun) {
            if ($bulan) {
                $q->whereMonth('tanggal', $bulan);
            }
            if ($tahun) {
                $q->whereYear('tanggal', $tahun);
            }
            $q->whereHas('mahasiswa', function($mq) {
                $mq->whereColumn('mahasiswa.pembimbing_id', '!=', 'dosen.id');
            });
        };

        return [
            'penguji1Sidangs as p1_ta1_count' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Seminar TA 1'); $filterBukanPembimbing($q); },
            'penguji2Sidangs as p2_ta1_count' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Seminar TA 1'); $filterBukanPembimbing($q); },
            'ketuaSidangs as ketua_ta1_count' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Seminar TA 1'); $filterBukanPembimbing($q); },

            'penguji1Sidangs as p1_ta2_count' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Sidang TA 2'); $filterBukanPembimbing($q); },
            'penguji2Sidangs as p2_ta2_count' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Sidang TA 2'); $filterBukanPembimbing($q); },
            'penguji3Sidangs as p3_ta2_count' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Sidang TA 2'); $filterBukanPembimbing($q); },
            'penguji4Sidangs as p4_ta2_count' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Sidang TA 2'); $filterBukanPembimbing($q); },
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Dosen')
                    ->alignCenter()
                    ->html()
                    ->formatStateUsing(fn (string $state): string => '<div style="text-align: left; width: 100%; min-width: 200px;">' . e($state) . '</div>')
                    ->searchable(),
                
                TextColumn::make('ta1')
                    ->label('TA 1')
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->getStateUsing(fn($record) => 
                        ($record->p1_ta1_count ?? 0) + 
                        ($record->p2_ta1_count ?? 0) + 
                        ($record->ketua_ta1_count ?? 0)
                    ),
                    
                TextColumn::make('ta2')
                    ->label('TA 2')
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->getStateUsing(fn($record) => 
                        ($record->p1_ta2_count ?? 0) + 
                        ($record->p2_ta2_count ?? 0) + 
                        ($record->p3_ta2_count ?? 0) + 
                        ($record->p4_ta2_count ?? 0)
                    ),
                    
                TextColumn::make('grand_total')
                    ->label('Total Keseluruhan')
                    ->alignCenter()
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(fn($record) => 
                        ($record->p1_ta1_count ?? 0) + 
                        ($record->p2_ta1_count ?? 0) + 
                        ($record->ketua_ta1_count ?? 0) +
                        ($record->p1_ta2_count ?? 0) + 
                        ($record->p2_ta2_count ?? 0) + 
                        ($record->p3_ta2_count ?? 0) + 
                        ($record->p4_ta2_count ?? 0)
                    )
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw('(IFNULL(p1_ta1_count,0) + IFNULL(p2_ta1_count,0) + IFNULL(ketua_ta1_count,0) + IFNULL(p1_ta2_count,0) + IFNULL(p2_ta2_count,0) + IFNULL(p3_ta2_count,0) + IFNULL(p4_ta2_count,0)) ' . $direction);
                    })
            ])
            ->modifyQueryUsing(function (Builder $query, \Filament\Tables\Table $table) {
                $livewire = $table->getLivewire();
                $bulan = $livewire->filterBulan ?? 'semua';
                $tahun = $livewire->filterTahun ?? 'semua';
                
                $bulan = $bulan === 'semua' ? null : $bulan;
                $tahun = $tahun === 'semua' ? null : $tahun;

                return $query->withCount(self::getWithCountMap($bulan, $tahun));
            })
            ->filters([])
            ->header(view('filament.custom-statistik-filters'))
            ->recordActions([])
            ->toolbarActions([])
            ->paginated([10, 25, 50, 100])
            ->defaultSort('grand_total', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStatistikDosen::route('/'),
        ];
    }
}
