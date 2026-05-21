<?php

namespace App\Filament\Resources\Sidang\Pages;

use App\Filament\Resources\Sidang\SidangResource;
use App\Models\Sidang;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ListSidang extends ListRecords
{
    protected static string $resource = SidangResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Sidang'),
            'seminar_ta_1' => Tab::make('Seminar TA 1')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_sidang', 'Seminar TA 1')),
            'seminar_ta_2' => Tab::make('Sidang TA 2')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_sidang', 'Sidang TA 2')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Simpan Jadwal')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->visible(fn () => auth()->user()?->isAdmin())
                ->form([
                    Select::make('bulan')
                        ->label('Pilih Bulan')
                        ->options([
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember'
                        ])
                        ->required(),
                    Select::make('jenis_sidang')
                        ->label('Jenis Sidang')
                        ->options([
                            'Seminar TA 1' => 'Seminar TA 1',
                            'Sidang TA 2' => 'Sidang TA 2',
                        ])
                        ->required(),
                    Select::make('kajur_id')
                        ->label('Ketua Jurusan')
                        ->options(\App\Models\Dosen::pluck('nama', 'id'))
                        ->searchable()
                        ->placeholder('Ketik nama kajur...')
                        ->required(),
                    Select::make('koordinator_id')
                        ->label('Koordinator Prodi')
                        ->options(\App\Models\Dosen::pluck('nama', 'id'))
                        ->searchable()
                        ->placeholder('Ketik nama koordinator...')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $bulan = $data['bulan'];
                    $jenis_sidang = $data['jenis_sidang'];
                    $kajur_id = $data['kajur_id'];
                    $koordinator_id = $data['koordinator_id'];
                    $tahun = date('Y');

                    $kajur = \App\Models\Dosen::find($kajur_id);
                    $koordinator = \App\Models\Dosen::find($koordinator_id);

                    $query = Sidang::with(['mahasiswa.pembimbing', 'penguji1', 'penguji2', 'ketuaSidang'])
                        ->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun)
                        ->where('jenis_sidang', $jenis_sidang)
                        ->orderBy('tanggal')
                        ->orderBy('ruangan')
                        ->orderBy('jam_mulai');

                    if ($jenis_sidang === 'Sidang TA 2') {
                        $query->with(['penguji3', 'penguji4']);
                    }

                    $sidangs = $query->get();

                    if ($sidangs->isEmpty()) {
                        Notification::make()
                            ->title('Jadwal Tidak Ditemukan')
                            ->body("Tidak ada jadwal untuk {$jenis_sidang} pada bulan {$bulan}.")
                            ->danger()
                            ->send();

                        return;
                    }

                    $groupedSidang = $sidangs->groupBy(function ($s) {
                        return $s->tanggal . '|' . $s->ruangan;
                    });

                    $bulanArray = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    $bulanNama = $bulanArray[$bulan];

                    Carbon::setLocale('id');

                    // 1. Generate Jadwal PDF
                    $viewTemplate = $jenis_sidang === 'Sidang TA 2' ? 'pdf.jadwal-seminar-ta2' : 'pdf.jadwal-seminar-ta1';
                    $pdfJadwal = Pdf::loadView($viewTemplate, [
                        'groupedSidang' => $groupedSidang,
                        'bulan_nama' => $bulanNama,
                        'tahun' => $tahun,
                        'jenis_sidang' => $jenis_sidang,
                        'kajur' => $kajur,
                    ])->setPaper('a4', 'landscape');

                    // 2. Prepare ZIP
                    $zip = new ZipArchive();
                    $filenameTitle = str_replace(' ', '_', $jenis_sidang);
                    $zipFileName = "Paket_Sidang_{$filenameTitle}_{$bulanNama}_{$tahun}.zip";
                    $zipPath = storage_path("app/public/{$zipFileName}");

                    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                        // Tambahkan Jadwal Utama
                        $zip->addFromString("Jadwal_{$filenameTitle}_{$bulanNama}_{$tahun}.pdf", $pdfJadwal->output());

                        // Tambahkan Berita Acara tiap mahasiswa
                        foreach ($sidangs as $s) {
                            $viewBA = $s->jenis_sidang === 'Sidang TA 2' ? 'pdf.berita-acara-ta2' : 'pdf.berita-acara-ta1';
                            $pdfBA = Pdf::loadView($viewBA, [
                                'sidang' => $s,
                                'kajur' => $kajur,
                                'koordinator' => $koordinator,
                            ])->setPaper('a4', 'portrait');

                            $nim = $s->mahasiswa->nim ?? 'unknown';
                            $nama = str_replace(' ', '_', $s->mahasiswa->nama ?? 'mhs');
                            // Taruh di folder Berita_Acara di dalam ZIP biar rapi
                            $zip->addFromString("Berita_Acara/BA_{$nim}_{$nama}.pdf", $pdfBA->output());
                        }

                        $zip->close();
                    }

                    return response()->download($zipPath)->deleteFileAfterSend(true);
                }),
            CreateAction::make()
                ->label('Tambahkan Sidang')
                ->visible(fn() => auth()->user()?->isAdmin()),
        ];
    }
}
