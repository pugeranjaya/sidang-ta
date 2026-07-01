<?php

namespace App\Filament\Resources\StatistikDosen;

use App\Filament\Resources\StatistikDosen\Pages\ManageStatistikDosen;
use App\Models\Dosen;
use App\Models\Mahasiswa;
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
use Filament\Tables\Columns\ColumnGroup;
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

    /**
     * Admin dan Dosen bisa akses halaman ini.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->isAdmin() || $user?->isDosen();
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
     * Hitung jumlah penguji, ketua sidang (hanya TA 1, bukan sebagai pembimbing).
     */
    public static function getWithCountMap(?string $jenisPeriode = null, ?string $bulan = null, ?string $minggu = null, ?string $tahun = null): array
    {
        $filterPeriode = function($q) use ($jenisPeriode, $bulan, $minggu, $tahun) {
            if ($bulan && $bulan !== 'semua') {
                $q->whereMonth('tanggal', $bulan);
            }
            if ($tahun && $tahun !== 'semua') {
                $q->whereYear('tanggal', $tahun);
            }
            
            if ($jenisPeriode === 'mingguan' && $minggu && $minggu !== 'semua') {
                $mingguInt = (int) $minggu;
                $startDay = ($mingguInt - 1) * 7 + 1;
                $endDay = $mingguInt === 5 ? 31 : $mingguInt * 7;
                $q->whereRaw("DAY(tanggal) BETWEEN ? AND ?", [$startDay, $endDay]);
            }
            
            $q->where('jenis_sidang', 'Seminar TA 1');
            $q->whereHas('mahasiswa', function($mq) {
                $mq->whereColumn('mahasiswa.pembimbing_id', '!=', 'dosen.id');
            });
        };

        $filterKumulatif = function($q) use ($tahun) {
            if ($tahun && $tahun !== 'semua') {
                $q->whereYear('tanggal', $tahun);
            }
            $q->where('jenis_sidang', 'Seminar TA 1');
            $q->whereHas('mahasiswa', function($mq) {
                $mq->whereColumn('mahasiswa.pembimbing_id', '!=', 'dosen.id');
            });
        };

        $filterPembimbingPeriode = function($q) use ($jenisPeriode, $bulan, $minggu, $tahun) {
            $q->whereHas('sidangs', function($sq) use ($jenisPeriode, $bulan, $minggu, $tahun) {
                $sq->where('jenis_sidang', 'Seminar TA 1');
                if ($bulan && $bulan !== 'semua') {
                    $sq->whereMonth('tanggal', $bulan);
                }
                if ($tahun && $tahun !== 'semua') {
                    $sq->whereYear('tanggal', $tahun);
                }
                
                if ($jenisPeriode === 'mingguan' && $minggu && $minggu !== 'semua') {
                    $mingguInt = (int) $minggu;
                    $startDay = ($mingguInt - 1) * 7 + 1;
                    $endDay = $mingguInt === 5 ? 31 : $mingguInt * 7;
                    $sq->whereRaw("EXTRACT(DAY FROM tanggal) BETWEEN ? AND ?", [$startDay, $endDay]);
                }
            });
        };

        $filterPembimbingKumulatif = function($q) use ($tahun) {
            $q->whereHas('sidangs', function($sq) use ($tahun) {
                $sq->where('jenis_sidang', 'Seminar TA 1');
                if ($tahun && $tahun !== 'semua') {
                    $sq->whereYear('tanggal', $tahun);
                }
            });
        };

        return [
            // PERIODE (Penguji = P1 + P2)
            'penguji1Sidangs as p1_periode_count' => $filterPeriode,
            'penguji2Sidangs as p2_periode_count' => $filterPeriode,
            'ketuaSidangs as ketua_periode_count' => $filterPeriode,
            'pembimbingMahasiswas as pembimbing_periode_count' => $filterPembimbingPeriode,

            // KUMULATIF
            'penguji1Sidangs as p1_kumulatif_count' => $filterKumulatif,
            'penguji2Sidangs as p2_kumulatif_count' => $filterKumulatif,
            'ketuaSidangs as ketua_kumulatif_count' => $filterKumulatif,
            'pembimbingMahasiswas as pembimbing_kumulatif_count' => $filterPembimbingKumulatif,
        ];
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
        $isDosen = $user?->isDosen() ?? false;

        $columns = [
            TextColumn::make('nama')
                ->label('Nama Dosen')
                ->alignCenter()
                ->html()
                ->formatStateUsing(fn (string $state): string => '<div style="text-align: left; width: 100%; min-width: 200px;">' . e($state) . '</div>')
                ->searchable(),

            ColumnGroup::make('Periode Terpilih')
                ->label(function ($livewire) {
                    $jenisPeriode = $livewire->filterJenisPeriode ?? 'bulanan';
                    $bulan = $livewire->filterBulan ?? 'semua';
                    $minggu = $livewire->filterMinggu ?? 'semua';
                    $tahun = $livewire->filterTahun ?? 'semua';
                    
                    if ($bulan === 'semua' && $tahun === 'semua') {
                        return 'Status Periode Keseluruhan';
                    }
                    
                    $namaBulan = '';
                    if ($bulan !== 'semua') {
                        $bulanArray = [
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];
                        $namaBulan = $bulanArray[$bulan] ?? '';
                    }
                    
                    $teksTahun = $tahun !== 'semua' ? $tahun : '';
                    $teksMinggu = ($jenisPeriode === 'mingguan' && $minggu !== 'semua') ? "M$minggu " : '';
                    
                    return trim("Status Periode $teksMinggu$namaBulan $teksTahun");
                })
                ->columns([
                TextColumn::make('pembimbing_periode')
                    ->label('Pembimbing')
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->getStateUsing(fn($record) => $record->pembimbing_periode_count ?? 0),
                TextColumn::make('ketua_periode')
                    ->label('Ketua Sidang')
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->getStateUsing(fn($record) => $record->ketua_periode_count ?? 0),
                TextColumn::make('penguji_periode')
                    ->label('Penguji')
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->getStateUsing(fn($record) => ($record->p1_periode_count ?? 0) + ($record->p2_periode_count ?? 0)),
            ])->alignCenter(),

            ColumnGroup::make('Kumulatif Tahun Terpilih', [
                TextColumn::make('pembimbing_kumulatif')
                    ->label('Pembimbing')
                    ->alignCenter()
                    ->getStateUsing(fn($record) => $record->pembimbing_kumulatif_count ?? 0),
                TextColumn::make('ketua_kumulatif')
                    ->label('Ketua Sidang')
                    ->alignCenter()
                    ->getStateUsing(fn($record) => $record->ketua_kumulatif_count ?? 0),
                TextColumn::make('penguji_kumulatif')
                    ->label('Penguji')
                    ->alignCenter()
                    ->getStateUsing(fn($record) => ($record->p1_kumulatif_count ?? 0) + ($record->p2_kumulatif_count ?? 0)),
            ])->alignCenter(),

            TextColumn::make('grand_total')
                ->label('Total Kumulatif Ketua & Penguji')
                ->alignCenter()
                ->badge()
                ->color('warning')
                ->getStateUsing(fn($record) => 
                    ($record->ketua_kumulatif_count ?? 0) + 
                    ($record->p1_kumulatif_count ?? 0) + 
                    ($record->p2_kumulatif_count ?? 0)
                )
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query->orderBy('ketua_kumulatif_count', $direction)
                                 ->orderBy('p1_kumulatif_count', $direction)
                                 ->orderBy('p2_kumulatif_count', $direction);
                }),
        ];

        return $table
            ->columns($columns)
            ->modifyQueryUsing(function (Builder $query, \Filament\Tables\Table $table) use ($isDosen) {
                $livewire = $table->getLivewire();
                $jenisPeriode = $livewire->filterJenisPeriode ?? 'bulanan';
                $bulan = $livewire->filterBulan ?? 'semua';
                $minggu = $livewire->filterMinggu ?? 'semua';
                $tahun = $livewire->filterTahun ?? 'semua';
                
                $bulan = $bulan === 'semua' ? null : $bulan;
                $minggu = $minggu === 'semua' ? null : $minggu;
                $tahun = $tahun === 'semua' ? null : $tahun;

                $query = $query->withCount(self::getWithCountMap($jenisPeriode, $bulan, $minggu, $tahun));

                // Dosen hanya lihat data diri sendiri
                if ($isDosen) {
                    $dosen = self::getLoggedInDosen();
                    if ($dosen) {
                        $query->where('id', $dosen->id);
                    } else {
                        $query->whereRaw('1 = 0'); // Tidak ada data jika dosen tidak ditemukan
                    }
                }

                return $query;
            })
            ->filters([])
            ->headerActions([
                \Filament\Actions\Action::make('exportExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->visible(fn() => auth()->user() && auth()->user()->isAdmin())
                    ->action(function (\Filament\Tables\Table $table) {
                        $livewire = $table->getLivewire();
                        $jenisPeriode = $livewire->filterJenisPeriode ?? 'bulanan';
                        $bulan = $livewire->filterBulan ?? 'semua';
                        $minggu = $livewire->filterMinggu ?? 'semua';
                        $tahun = $livewire->filterTahun ?? 'semua';
                        
                        $bulan = $bulan === 'semua' ? null : $bulan;
                        $minggu = $minggu === 'semua' ? null : $minggu;
                        $tahun = $tahun === 'semua' ? null : $tahun;

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\StatistikDosenExport($jenisPeriode, $bulan, $minggu, $tahun),
                            'Matriks_Pembimbing_Penguji_' . date('Ymd_His') . '.xlsx'
                        );
                    }),
                \Filament\Actions\Action::make('customFilters')
                    ->label('')
                    ->view('filament.custom-statistik-filters')
            ])
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
