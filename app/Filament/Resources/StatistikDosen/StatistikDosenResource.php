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

    public static function table(Table $table): Table
    {
        $months = \App\Models\Sidang::selectRaw('DISTINCT DATE_FORMAT(tanggal, "%Y-%m") as bulan')
            ->whereNotNull('tanggal')
            ->orderBy('bulan')
            ->pluck('bulan')
            ->toArray();

        $columns = [
            TextColumn::make('nama')
                ->label('Nama Dosen')
                ->alignCenter()
                ->extraAttributes([
                    'style' => 'text-align: left !important; justify-content: flex-start !important;',
                ])
                ->searchable()
                ->sortable(),
            TextColumn::make('nip')
                ->label('NIP')
                ->alignCenter()
                ->extraAttributes([
                    'style' => 'text-align: left !important; justify-content: flex-start !important;',
                ])
                ->searchable(),
        ];

        $filterBukanPembimbing = function($q) {
            $q->whereHas('mahasiswa', function($mq) {
                $mq->whereColumn('mahasiswa.pembimbing_id', '!=', 'dosen.id');
            });
        };

        $withCountMap = [
            'penguji1Sidangs as p1_ta1' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Seminar TA 1'); $filterBukanPembimbing($q); },
            'penguji2Sidangs as p2_ta1' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Seminar TA 1'); $filterBukanPembimbing($q); },
            'ketuaSidangs as ketua_ta1' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Seminar TA 1'); $filterBukanPembimbing($q); },

            'penguji1Sidangs as p1_ta2' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Sidang TA 2'); $filterBukanPembimbing($q); },
            'penguji2Sidangs as p2_ta2' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Sidang TA 2'); $filterBukanPembimbing($q); },
            'penguji3Sidangs as p3_ta2' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Sidang TA 2'); $filterBukanPembimbing($q); },
            'penguji4Sidangs as p4_ta2' => function($q) use ($filterBukanPembimbing) { $q->where('jenis_sidang', 'Sidang TA 2'); $filterBukanPembimbing($q); },
        ];

        $monthColumns = [];

        foreach ($months as $bulan) {
            $monthLabel = \Illuminate\Support\Carbon::parse($bulan . '-01')->translatedFormat('M-y'); // Jan-26 dll
            $aliasBulan = str_replace('-', '_', $bulan);
            
            // TA 1
            $withCountMap["penguji1Sidangs as p1_ta1_{$aliasBulan}"] = function($q) use ($bulan, $filterBukanPembimbing) {
                $startDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->startOfMonth()->toDateString();
                $endDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->endOfMonth()->toDateString();
                $q->whereBetween('tanggal', [$startDate, $endDate])->where('jenis_sidang', 'Seminar TA 1');
                $filterBukanPembimbing($q);
            };
            $withCountMap["penguji2Sidangs as p2_ta1_{$aliasBulan}"] = function($q) use ($bulan, $filterBukanPembimbing) {
                $startDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->startOfMonth()->toDateString();
                $endDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->endOfMonth()->toDateString();
                $q->whereBetween('tanggal', [$startDate, $endDate])->where('jenis_sidang', 'Seminar TA 1');
                $filterBukanPembimbing($q);
            };
            $withCountMap["ketuaSidangs as ketua_ta1_{$aliasBulan}"] = function($q) use ($bulan, $filterBukanPembimbing) {
                $startDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->startOfMonth()->toDateString();
                $endDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->endOfMonth()->toDateString();
                $q->whereBetween('tanggal', [$startDate, $endDate])->where('jenis_sidang', 'Seminar TA 1');
                $filterBukanPembimbing($q);
            };

            // TA 2
            $withCountMap["penguji1Sidangs as p1_ta2_{$aliasBulan}"] = function($q) use ($bulan, $filterBukanPembimbing) {
                $startDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->startOfMonth()->toDateString();
                $endDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->endOfMonth()->toDateString();
                $q->whereBetween('tanggal', [$startDate, $endDate])->where('jenis_sidang', 'Sidang TA 2');
                $filterBukanPembimbing($q);
            };
            $withCountMap["penguji2Sidangs as p2_ta2_{$aliasBulan}"] = function($q) use ($bulan, $filterBukanPembimbing) {
                $startDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->startOfMonth()->toDateString();
                $endDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->endOfMonth()->toDateString();
                $q->whereBetween('tanggal', [$startDate, $endDate])->where('jenis_sidang', 'Sidang TA 2');
                $filterBukanPembimbing($q);
            };
            $withCountMap["penguji3Sidangs as p3_ta2_{$aliasBulan}"] = function($q) use ($bulan, $filterBukanPembimbing) {
                $startDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->startOfMonth()->toDateString();
                $endDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->endOfMonth()->toDateString();
                $q->whereBetween('tanggal', [$startDate, $endDate])->where('jenis_sidang', 'Sidang TA 2');
                $filterBukanPembimbing($q);
            };
            $withCountMap["penguji4Sidangs as p4_ta2_{$aliasBulan}"] = function($q) use ($bulan, $filterBukanPembimbing) {
                $startDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->startOfMonth()->toDateString();
                $endDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->endOfMonth()->toDateString();
                $q->whereBetween('tanggal', [$startDate, $endDate])->where('jenis_sidang', 'Sidang TA 2');
                $filterBukanPembimbing($q);
            };

            $monthColumns[] = \Filament\Tables\Columns\ColumnGroup::make($monthLabel, [
                TextColumn::make("ta1_{$aliasBulan}")
                    ->label('TA 1')
                    ->alignCenter()
                    ->getStateUsing(fn($record) => 
                        ($record->{"p1_ta1_{$aliasBulan}"} ?? 0) + 
                        ($record->{"p2_ta1_{$aliasBulan}"} ?? 0) + 
                        ($record->{"ketua_ta1_{$aliasBulan}"} ?? 0)
                    ),
                TextColumn::make("ta2_{$aliasBulan}")
                    ->label('TA 2')
                    ->alignCenter()
                    ->getStateUsing(fn($record) => 
                        ($record->{"p1_ta2_{$aliasBulan}"} ?? 0) + 
                        ($record->{"p2_ta2_{$aliasBulan}"} ?? 0) + 
                        ($record->{"p3_ta2_{$aliasBulan}"} ?? 0) + 
                        ($record->{"p4_ta2_{$aliasBulan}"} ?? 0)
                    ),
                TextColumn::make("total_{$aliasBulan}")
                    ->label('Total')
                    ->alignCenter()
                    ->weight('bold')
                    ->color('primary')
                    ->getStateUsing(fn($record) => 
                        ($record->{"p1_ta1_{$aliasBulan}"} ?? 0) + 
                        ($record->{"p2_ta1_{$aliasBulan}"} ?? 0) + 
                        ($record->{"ketua_ta1_{$aliasBulan}"} ?? 0) +
                        ($record->{"p1_ta2_{$aliasBulan}"} ?? 0) + 
                        ($record->{"p2_ta2_{$aliasBulan}"} ?? 0) + 
                        ($record->{"p3_ta2_{$aliasBulan}"} ?? 0) + 
                        ($record->{"p4_ta2_{$aliasBulan}"} ?? 0)
                    ),
            ])->alignCenter();
        }

        if (!empty($monthColumns)) {
            $columns = array_merge($columns, $monthColumns);
        }

        $columns[] = \Filament\Tables\Columns\ColumnGroup::make('Total Keseluruhan', [
            TextColumn::make('grand_total_ta1')
                ->label('Total TA 1')
                ->alignCenter()
                ->getStateUsing(fn($record) => 
                    ($record->p1_ta1 ?? 0) + 
                    ($record->p2_ta1 ?? 0) + 
                    ($record->ketua_ta1 ?? 0)
                ),
            TextColumn::make('grand_total_ta2')
                ->label('Total TA 2')
                ->alignCenter()
                ->getStateUsing(fn($record) => 
                    ($record->p1_ta2 ?? 0) + 
                    ($record->p2_ta2 ?? 0) + 
                    ($record->p3_ta2 ?? 0) + 
                    ($record->p4_ta2 ?? 0)
                ),
            TextColumn::make('grand_total')
                ->label('Total Semua')
                ->alignCenter()
                ->weight('bold')
                ->color('success')
                ->getStateUsing(fn($record) => 
                    ($record->p1_ta1 ?? 0) + 
                    ($record->p2_ta1 ?? 0) + 
                    ($record->ketua_ta1 ?? 0) +
                    ($record->p1_ta2 ?? 0) + 
                    ($record->p2_ta2 ?? 0) + 
                    ($record->p3_ta2 ?? 0) + 
                    ($record->p4_ta2 ?? 0)
                )
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query->orderByRaw('(IFNULL(p1_ta1,0) + IFNULL(p2_ta1,0) + IFNULL(ketua_ta1,0) + IFNULL(p1_ta2,0) + IFNULL(p2_ta2,0) + IFNULL(p3_ta2,0) + IFNULL(p4_ta2,0)) ' . $direction);
                })
        ])->alignCenter();

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($withCountMap) {
                return $query->withCount($withCountMap);
            })
            ->columns($columns)
            ->filters([])
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
