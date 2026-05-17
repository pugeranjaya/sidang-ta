<?php

namespace App\Filament\Widgets;

use App\Models\Dosen;
use App\Models\Sidang;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class DosenScheduleWidget extends TableWidget
{
    protected static ?string $heading = 'Jadwal Menguji';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        return auth()->user()?->role === 'dosen';
    }

    public function table(Table $table): Table
    {
        $userEmail = auth()->user()?->email;
        $dosen = Dosen::where('email', $userEmail)->first();

        return $table
            ->query(function () use ($dosen): Builder {
                if (!$dosen) {
                    return Sidang::query()->whereNull('id'); // Return empty query
                }

                return Sidang::query()
                    ->where(function ($query) use ($dosen) {
                        $query->where('penguji1_id', $dosen->id)
                              ->orWhere('penguji2_id', $dosen->id)
                              ->orWhere('penguji3_id', $dosen->id)
                              ->orWhere('penguji4_id', $dosen->id)
                              ->orWhere('ketua_sidang_id', $dosen->id);
                    })
                    ->whereBetween('tanggal', [
                        now()->toDateString(), 
                        now()->addMonth()->endOfMonth()->toDateString()
                    ])
                    ->orderBy('tanggal', 'asc')
                    ->orderBy('jam_mulai', 'asc');
            })
            ->columns([
                TextColumn::make('mahasiswa.nama')
                    ->label('Mahasiswa'),
                TextColumn::make('jenis_sidang')
                    ->label('Jenis Sidang')
                    ->badge(),
                TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('jam_mulai')
                    ->time('H:i'),
                TextColumn::make('ruangan'),
                TextColumn::make('peran')
                    ->label('Peran')
                    ->getStateUsing(function (Sidang $record) use ($dosen) {
                        if (!$dosen) return '-';
                        if ($record->ketua_sidang_id === $dosen->id) return 'Ketua Sidang';
                        if ($record->penguji1_id === $dosen->id) return 'Penguji 1';
                        if ($record->penguji2_id === $dosen->id) return 'Penguji 2';
                        if ($record->penguji3_id === $dosen->id) return 'Penguji 3';
                        if ($record->penguji4_id === $dosen->id) return 'Penguji 4';
                        return 'Penguji';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Ketua Sidang' => 'primary',
                        default => 'success',
                    }),
            ])
            ->paginated([5, 10, 25])
            ->emptyStateHeading('Belum ada jadwal sidang');
    }
}
