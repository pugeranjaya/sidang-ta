<?php

namespace App\Filament\Resources\Mahasiswa\Pages;

use App\Filament\Resources\Mahasiswa\MahasiswaResource;
use App\Imports\MahasiswaImport;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListMahasiswa extends ListRecords
{
    protected static string $resource = MahasiswaResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            // ── Import dari Google Form Excel ───────────────────────────────
            Action::make('importGoogleForm')
                ->label('Import Data Mahasiswa')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Data Mahasiswa')
                ->modalDescription('Upload file Excel (.xlsx/.xls)')
                ->modalWidth('xl')
                ->form([
                    \Filament\Forms\Components\TextInput::make('sheet_name')
                        ->label('Nama Sheet (Opsional)')
                        ->placeholder('Contoh: Matriks mulai Mei 2024')
                        ->helperText('Kosongkan jika ingin mengimpor sheet pertama saja.'),

                    FileUpload::make('file')
                        ->label('File Excel Google Form')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->disk('local')
                        ->directory('mahasiswa-imports')
                        ->storeFileNamesIn('original_filename')
                        ->maxSize(10240) // 10 MB
                        ->required()
                        ->helperText('Format yang diterima: .xlsx atau .xls (maks. 10 MB)'),
                ])
                ->modalSubmitActionLabel('Import Sekarang')
                ->action(function (array $data): void {
                    $filePath = Storage::disk('local')->path($data['file']);

                    $importer = new MahasiswaImport($data['sheet_name'] ?? null);

                    try {
                        Excel::import($importer, $filePath);

                        $sheet = $importer->sheetImport;
                        $lines = [];

                        if ($sheet->imported > 0) {
                            $lines[] = "✅ {$sheet->imported} mahasiswa baru berhasil ditambahkan.";
                        }
                        if ($sheet->updated > 0) {
                            $lines[] = "🔄 {$sheet->updated} data mahasiswa diperbarui.";
                        }
                        if ($sheet->skipped > 0) {
                            $lines[] = "⚠️ {$sheet->skipped} baris dilewati karena error.";
                        }
                        if (!empty($sheet->notFound)) {
                            $lines[] = "❌ Pembimbing tidak ditemukan di database:\n• " .
                                implode("\n• ", $sheet->notFound);
                        }

                        $total = $sheet->imported + $sheet->updated;

                        if ($total > 0) {
                            Notification::make()
                                ->title('Import Berhasil')
                                ->body(implode("\n", $lines))
                                ->success()
                                ->duration(8000)
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import Selesai (Tidak Ada Data Baru)')
                                ->body(implode("\n", $lines) ?: 'Tidak ada baris yang diproses. Pastikan format kolom file sesuai.')
                                ->warning()
                                ->duration(8000)
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->duration(10000)
                            ->send();
                    } finally {
                        // Hapus file temp setelah import
                        Storage::disk('local')->delete($data['file']);
                    }
                }),

            // ── Tambah manual ───────────────────────────────────────────────
            Actions\CreateAction::make()
                ->label('Tambahkan Mahasiswa'),
        ];
    }
}
