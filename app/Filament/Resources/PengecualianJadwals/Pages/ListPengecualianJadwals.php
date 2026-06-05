<?php

namespace App\Filament\Resources\PengecualianJadwals\Pages;

use App\Filament\Resources\PengecualianJadwals\PengecualianJadwalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengecualianJadwals extends ListRecords
{
    protected static string $resource = PengecualianJadwalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
