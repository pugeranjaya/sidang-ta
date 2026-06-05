<?php

namespace App\Filament\Resources\PengecualianJadwals\Pages;

use App\Filament\Resources\PengecualianJadwals\PengecualianJadwalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPengecualianJadwal extends EditRecord
{
    protected static string $resource = PengecualianJadwalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
