<?php

namespace App\Filament\Resources\JadwalMengajar\Pages;

use App\Filament\Resources\JadwalMengajar\JadwalMengajarResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJadwalMengajar extends CreateRecord
{
    protected static string $resource = JadwalMengajarResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
