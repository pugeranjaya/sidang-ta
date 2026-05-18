<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use App\Services\SidangScheduler;
use App\Models\Mahasiswa;
use App\Models\Sidang;
use Filament\Forms\Concerns\InteractsWithForms;

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
    public ?array $scheduleOptions = null;
    public ?string $debugLogText = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
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
                                $m->id => "{$m->nama} ({$m->nim}) - {$m->rumpun_ta}"
                            ])
                            ->toArray()
                    )
                    ->getOptionLabelUsing(
                        fn($value): ?string =>
                        Mahasiswa::find($value)?->nama
                    )
                    ->required(),

                Select::make('jenis_sidang')
                    ->label('Jenis Sidang')
                    ->options([
                        'Seminar TA 1' => 'Seminar TA 1',
                        'Sidang TA 2' => 'Sidang TA 2',
                    ])
                    ->default('Seminar TA 1')
                    ->required(),

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
        $this->debugLogText = null;

        $scheduler = app(SidangScheduler::class);

        try {
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
                $lastLog = end($debugLog);
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

    public function simpanJadwal(int $index): void
    {
        if (!$this->scheduleOptions || !isset($this->scheduleOptions[$index])) {
            return;
        }

        $selected = $this->scheduleOptions[$index];

        try {
            $dbData = [
                'mahasiswa_id' => $selected['mahasiswa_id'],
                'jenis_sidang' => $selected['jenis_sidang'],
                'tanggal' => $selected['tanggal'],
                'jam_mulai' => $selected['jam_mulai'],
                'jam_selesai' => $selected['jam_selesai'],
                'ruangan' => $selected['ruangan'],
                'penguji1_id' => $selected['penguji1_id'],
                'penguji2_id' => $selected['penguji2_id'],
                'ketua_sidang_id' => $selected['ketua_sidang_id'],
                'status' => 'belum lulus',
            ];

            if ($selected['jenis_sidang'] === 'Sidang TA 2') {
                $dbData['penguji3_id'] = $selected['penguji3_id'];
                $dbData['penguji4_id'] = $selected['penguji4_id'];
            }

            Sidang::create($dbData);

            $this->scheduleOptions = null;
            $this->debugLogText = null;
            
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
}
