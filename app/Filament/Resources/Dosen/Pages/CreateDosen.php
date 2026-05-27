<?php

namespace App\Filament\Resources\Dosen\Pages;

use App\Filament\Resources\Dosen\DosenResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDosen extends CreateRecord
{
    protected static string $resource = DosenResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
