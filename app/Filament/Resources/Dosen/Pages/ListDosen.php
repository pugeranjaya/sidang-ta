<?php

namespace App\Filament\Resources\Dosen\Pages;

use App\Filament\Resources\Dosen\DosenResource;
use App\Imports\DosenImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListDosen extends ListRecords
{
    protected static string $resource = DosenResource::class;

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Data Dosen';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importExcel')
                ->label('Import Data Dosen')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
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
                        $import = new DosenImport();

                        // Laravel-Excel bawaan mendukung pembacaan nama disk yang di setting di FileUpload sebelumnya
                        Excel::import($import, $data['file'], 'local');

                        // Opsional: Hapus file agar folder storage tidak menumpuk file sampah hasil import
                        \Illuminate\Support\Facades\Storage::disk('local')->delete($data['file']);

                        $count = $import->getImportedCount();

                        Notification::make()
                            ->title('Import Selesai')
                            ->body("Terbaca {$count} baris data.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        // Jika ada error (termasuk Exception Custom yang kita buat jika header tidak sesuai)
                        Notification::make()
                            ->title('Gagal mengimpor file Excel')
                            ->body(str_replace('GATAL/ERROR: ', '', $e->getMessage()))
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),

            CreateAction::make()
                ->label('Tambahkan Dosen'),
        ];
    }
}
