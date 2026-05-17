<?php

namespace App\Filament\Resources\Mahasiswa\Pages;

use App\Filament\Resources\Mahasiswa\MahasiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMahasiswa extends EditRecord
{
    protected static string $resource = MahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
