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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['rumpun_keahlian'])) {
            $data['rumpun_keahlian'] = (array) $data['rumpun_keahlian'];
        }

        return $data;
    }
}
