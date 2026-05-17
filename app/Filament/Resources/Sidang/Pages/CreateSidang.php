<?php

namespace App\Filament\Resources\Sidang\Pages;

use App\Filament\Resources\Sidang\SidangResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSidang extends CreateRecord
{
    protected static string $resource = SidangResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
