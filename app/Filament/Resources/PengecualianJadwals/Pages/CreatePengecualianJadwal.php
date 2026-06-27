<?php

namespace App\Filament\Resources\PengecualianJadwals\Pages;

use App\Filament\Resources\PengecualianJadwals\PengecualianJadwalResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePengecualianJadwal extends CreateRecord
{
    protected static string $resource = PengecualianJadwalResource::class;

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()->hidden();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
