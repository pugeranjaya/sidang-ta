<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use App\Services\SidangScheduler;
use App\Models\Mahasiswa;
use App\Models\Sidang;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Components\Utilities\Get;

class GenerateJadwalSidang extends Page
{
    use InteractsWithForms;

    protected static ?string $title = 'Buat Jadwal Sidang';
    protected ?string $heading = 'Buat Jadwal Sidang';

    protected static ?string $navigationLabel = 'Buat Jadwal Sidang';
    protected static ?int $navigationSort = 5;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar-days';

    protected string $view = 'filament.pages.generate-jadwal-sidang';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public ?array $data = [];

    /** Untuk mode single: array opsi jadwal yang bisa dipilih */
    public ?array $scheduleOptions = null;

    /** Untuk mode batch: array hasil per mahasiswa */
    public ?array $batchResults = null;

    public ?string $debugLogText = null;

    /** Flag mode: 'single' | 'batch' */
    public string $mode = 'single';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('jenis_sidang')
                    ->label('Jenis Sidang')
                    ->options([
                        'Seminar TA 1' => 'Seminar TA 1',
                        'Sidang TA 2'  => 'Sidang TA 2',
                    ])
                    ->default('Seminar TA 1')
                    ->live()
                    ->required(),

                Toggle::make('mode_batch')
                    ->label('Mode Batch (jadwalkan lebih dari 1 mahasiswa sekaligus)')
                    ->helperText('Aktifkan untuk memilih hingga 15 mahasiswa (5 per ruangan × 3 ruangan).')
                    ->default(false)
                    ->live(),

                // ── Mode Single: 1 mahasiswa ────────────────────────────────
                Select::make('mahasiswa_id')
                    ->label('Pilih Mahasiswa')
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn(string $search): array =>
                        Mahasiswa::where('nama', 'like', "%{$search}%")
                            ->orWhere('nim', 'like', "%{$search}%")
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(fn($m) => [
                                $m->id => "{$m->nama} ({$m->nim}) — {$m->rumpun_ta}"
                            ])
                            ->toArray()
                    )
                    ->getOptionLabelUsing(
                        fn($value): ?string =>
                        Mahasiswa::find($value) ? Mahasiswa::find($value)->nama . ' (' . Mahasiswa::find($value)->nim . ')' : null
                    )
                    ->visible(fn(Get $get) => !$get('mode_batch'))
                    ->required(fn(Get $get) => !$get('mode_batch')),

                // ── Mode Batch: multi mahasiswa ─────────────────────────────
                Select::make('mahasiswa_ids')
                    ->label('Pilih Mahasiswa (Multi)')
                    ->helperText('Pilih 1–15 mahasiswa. Sistem akan otomatis mendistribusikan ke 3 ruangan (maks 5 per ruangan).')
                    ->multiple()
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn(string $search): array =>
                        Mahasiswa::where('nama', 'like', "%{$search}%")
                            ->orWhere('nim', 'like', "%{$search}%")
                            ->limit(30)
                            ->get()
                            ->mapWithKeys(fn($m) => [
                                $m->id => "{$m->nama} ({$m->nim}) — {$m->rumpun_ta}"
                            ])
                            ->toArray()
                    )
                    ->getOptionLabelsUsing(
                        fn(array $values): array =>
                        Mahasiswa::findMany($values)
                            ->mapWithKeys(fn($m) => [
                                $m->id => "{$m->nama} ({$m->nim})"
                            ])
                            ->toArray()
                    )
                    ->visible(fn(Get $get) => (bool) $get('mode_batch'))
                    ->required(fn(Get $get) => (bool) $get('mode_batch')),

                DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->default(now()->format('Y-m-d')),

                DatePicker::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->required()
                    ->default(now()->addWeeks(2)->format('Y-m-d')),
            ])
            ->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();
        $this->scheduleOptions = null;
        $this->batchResults    = null;
        $this->debugLogText    = null;
        $isBatch = (bool) ($data['mode_batch'] ?? false);

        $scheduler = app(SidangScheduler::class);

        try {
            if ($isBatch) {
                $this->mode = 'batch';
                $mahasiswaIds = $data['mahasiswa_ids'] ?? [];

                if (empty($mahasiswaIds)) {
                    Notification::make()
                        ->title('Pilih minimal 1 mahasiswa')
                        ->warning()
                        ->send();
                    return;
                }

                if (count($mahasiswaIds) > 15) {
                    Notification::make()
                        ->title('Maksimal 15 mahasiswa per batch')
                        ->body('Pilih maksimal 15 mahasiswa (5 per ruangan × 3 ruangan).')
                        ->danger()
                        ->send();
                    return;
                }

                $result = $scheduler->generateBatch(
                    array_values($mahasiswaIds),
                    $data['tanggal_mulai'],
                    $data['tanggal_selesai'],
                    $data['jenis_sidang']
                );

                $debugLog = $result['_debug'] ?? $scheduler->debugLog;
                $this->debugLogText = !empty($debugLog) ? implode("\n", $debugLog) : null;

                if (!empty($result['results'])) {
                    $this->batchResults = $result['results'];
                    $berhasil = collect($result['results'])->filter(fn($r) => $r['success'] ?? false)->count();
                    $gagal    = count($result['results']) - $berhasil;

                    $msg = "✅ {$berhasil} jadwal berhasil digenerate.";
                    if ($gagal > 0) {
                        $msg .= " ⚠️ {$gagal} mahasiswa tidak mendapat slot.";
                    }

                    Notification::make()
                        ->title('Batch Generate Selesai')
                        ->body($msg)
                        ->success()
                        ->duration(8000)
                        ->send();
                } else {
                    Notification::make()
                        ->title('❌ Gagal generate jadwal batch')
                        ->body($result['error'] ?? 'Terjadi kesalahan pada algoritma.')
                        ->danger()
                        ->duration(15000)
                        ->send();
                }
            } else {
                $this->mode = 'single';
                $result = $scheduler->generateForMahasiswa(
                    $data['mahasiswa_id'],
                    $data['tanggal_mulai'],
                    $data['tanggal_selesai'],
                    $data['jenis_sidang']
                );

                $debugLog = $result['_debug'] ?? $scheduler->debugLog;
                $this->debugLogText = !empty($debugLog) ? implode("\n", $debugLog) : null;

                if (isset($result['success']) && $result['success'] && !empty($result['options'])) {
                    $this->scheduleOptions = $result['options'];

                    Notification::make()
                        ->title('✅ ' . count($this->scheduleOptions) . ' Opsi Jadwal ditemukan!')
                        ->body('Silakan pilih salah satu opsi jadwal yang disarankan di bawah ini.')
                        ->success()
                        ->send();
                } else {
                    $lastLog  = end($debugLog);
                    $errorMsg = $lastLog ?: 'Terjadi kesalahan pada algoritma penjadwalan.';

                    if (strpos($errorMsg, 'GAGAL: ') === 0) {
                        $errorMsg = substr($errorMsg, 7);
                    }

                    Notification::make()
                        ->title('❌ Gagal generate jadwal')
                        ->body($errorMsg)
                        ->danger()
                        ->duration(15000)
                        ->send();
                }
            }
        } catch (\Exception $e) {
            $debugLog = $scheduler->debugLog ?? [];
            $this->debugLogText = !empty($debugLog) ? implode("\n", $debugLog) : null;

            Notification::make()
                ->title('⚠️ Terjadi Kesalahan')
                ->body($e->getMessage())
                ->danger()
                ->duration(30000)
                ->send();
        }
    }

    /** Simpan satu opsi jadwal (mode single) */
    public function simpanJadwal(int $index): void
    {
        if (!$this->scheduleOptions || !isset($this->scheduleOptions[$index])) {
            return;
        }

        $selected = $this->scheduleOptions[$index];

        try {
            $dbData = [
                'mahasiswa_id'    => $selected['mahasiswa_id'],
                'jenis_sidang'    => $selected['jenis_sidang'],
                'tanggal'         => $selected['tanggal'],
                'jam_mulai'       => $selected['jam_mulai'],
                'jam_selesai'     => $selected['jam_selesai'],
                'ruangan'         => $selected['ruangan'],
                'penguji1_id'     => $selected['penguji1_id'],
                'penguji2_id'     => $selected['penguji2_id'],
                'ketua_sidang_id' => $selected['ketua_sidang_id'],
                'status'          => 'belum lulus',
            ];

            if ($selected['jenis_sidang'] === 'Sidang TA 2') {
                $dbData['penguji3_id'] = $selected['penguji3_id'];
                $dbData['penguji4_id'] = $selected['penguji4_id'];
            }

            Sidang::create($dbData);

            $this->scheduleOptions = null;
            $this->debugLogText    = null;
            $this->form->fill();

            Notification::make()
                ->title('✅ Jadwal sidang berhasil disimpan!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Gagal menyimpan jadwal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /** Simpan semua hasil batch sekaligus (hanya yang success) */
    public function simpanSemuaBatch(): void
    {
        if (!$this->batchResults) {
            return;
        }

        $disimpan = 0;
        $gagal    = 0;

        foreach ($this->batchResults as $mhsId => $result) {
            if (!($result['success'] ?? false)) {
                continue;
            }

            try {
                $dbData = [
                    'mahasiswa_id'    => $result['mahasiswa_id'],
                    'jenis_sidang'    => $result['jenis_sidang'],
                    'tanggal'         => $result['tanggal'],
                    'jam_mulai'       => $result['jam_mulai'],
                    'jam_selesai'     => $result['jam_selesai'],
                    'ruangan'         => $result['ruangan'],
                    'penguji1_id'     => $result['penguji1_id'],
                    'penguji2_id'     => $result['penguji2_id'],
                    'ketua_sidang_id' => $result['ketua_sidang_id'],
                    'status'          => 'belum lulus',
                ];

                if (($result['jenis_sidang'] ?? '') === 'Sidang TA 2') {
                    $dbData['penguji3_id'] = $result['penguji3_id'] ?? null;
                    $dbData['penguji4_id'] = $result['penguji4_id'] ?? null;
                }

                Sidang::create($dbData);
                $disimpan++;
            } catch (\Exception $e) {
                $gagal++;
            }
        }

        $this->batchResults = null;
        $this->debugLogText = null;
        $this->form->fill();

        $msg = "✅ {$disimpan} jadwal berhasil disimpan.";
        if ($gagal > 0) {
            $msg .= " ❌ {$gagal} jadwal gagal disimpan.";
        }

        Notification::make()
            ->title('Batch Tersimpan')
            ->body($msg)
            ->success()
            ->duration(8000)
            ->send();
    }
}
