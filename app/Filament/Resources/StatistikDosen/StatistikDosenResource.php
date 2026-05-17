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

        $withCountMap = [
            'penguji1Sidangs',
            'penguji2Sidangs',
            'ketuaSidangs'
        ];

        $monthColumns = [];

        foreach ($months as $bulan) {
            $monthLabel = \Illuminate\Support\Carbon::parse($bulan . '-01')->translatedFormat('M-y'); // Jan-26 dll
            $aliasBulan = str_replace('-', '_', $bulan);
            
            $withCountMap["penguji1Sidangs as p1_{$aliasBulan}"] = function($q) use ($bulan) {
                $startDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->startOfMonth()->toDateString();
                $endDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->endOfMonth()->toDateString();
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            };
            $withCountMap["penguji2Sidangs as p2_{$aliasBulan}"] = function($q) use ($bulan) {
                $startDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->startOfMonth()->toDateString();
                $endDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->endOfMonth()->toDateString();
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            };
            $withCountMap["ketuaSidangs as ketua_{$aliasBulan}"] = function($q) use ($bulan) {
                $startDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->startOfMonth()->toDateString();
                $endDate = \Illuminate\Support\Carbon::parse($bulan . '-01')->endOfMonth()->toDateString();
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            };

            $monthColumns[] = TextColumn::make("total_{$aliasBulan}")
                ->label($monthLabel)
                ->alignCenter()
                ->getStateUsing(fn($record) => 
                    ($record->{"p1_{$aliasBulan}"} ?? 0) + 
                    ($record->{"p2_{$aliasBulan}"} ?? 0) + 
                    ($record->{"ketua_{$aliasBulan}"} ?? 0)
                );
        }

        if (!empty($monthColumns)) {
            $columns[] = \Filament\Tables\Columns\ColumnGroup::make('Total Menguji', $monthColumns)->alignCenter();
        }

        $columns[] = TextColumn::make('grand_total')
            ->label('Total Keseluruhan')
            ->alignCenter()
            ->weight('bold')
            ->getStateUsing(fn($record) => 
                ($record->penguji1_sidangs_count ?? 0) + 
                ($record->penguji2_sidangs_count ?? 0) + 
                ($record->ketua_sidangs_count ?? 0)
            )
            ->sortable(query: function (Builder $query, string $direction): Builder {
                return $query->orderByRaw('(penguji1_sidangs_count + penguji2_sidangs_count + ketua_sidangs_count) ' . $direction);
            });

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
