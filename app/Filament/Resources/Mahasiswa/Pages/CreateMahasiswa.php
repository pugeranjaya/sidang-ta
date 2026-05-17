<?php

namespace App\Filament\Resources\Mahasiswa\Pages;

use App\Filament\Resources\Mahasiswa\MahasiswaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMahasiswa extends CreateRecord
{
    protected static string $resource = MahasiswaResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
