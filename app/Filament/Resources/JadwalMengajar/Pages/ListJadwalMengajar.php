<?php

namespace App\Filament\Resources\JadwalMengajar\Pages;

use App\Filament\Resources\JadwalMengajar\JadwalMengajarResource;
use App\Imports\JadwalMengajarImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListJadwalMengajar extends ListRecords
{
    protected static string $resource = JadwalMengajarResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importExcel')
                ->label('Import Data Jadwal Mengajar')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->visible(fn() => auth()->user()?->isAdmin())
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel (.xlsx / .xls)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->disk('local')
                        ->directory('imports')
                        ->required()
                ])
                ->action(function (array $data) {
                    try {
                        $import = new JadwalMengajarImport();
                        Excel::import($import, $data['file'], 'local');

                        // Opsional: Hapus file dari folder temporari
                        \Illuminate\Support\Facades\Storage::disk('local')->delete($data['file']);

                        $count = $import->getImportedCount();

                        Notification::make()
                            ->title('Import Selesai')
                            ->body("Berhasil: {$count} baris data berhasil diperbarui atau ditambahkan (jika nama dosen dikenali).")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal mengimpor file Excel')
                            ->body(str_replace('GATAL/ERROR: ', '', $e->getMessage()))
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),

            CreateAction::make()
                ->label('Tambahkan Jadwal')
                ->visible(fn() => auth()->user()?->isAdmin()),
        ];
    }
}
