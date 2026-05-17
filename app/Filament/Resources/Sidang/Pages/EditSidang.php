<?php

namespace App\Filament\Resources\Sidang\Pages;

use App\Filament\Resources\Sidang\SidangResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSidang extends EditRecord
{
    protected static string $resource = SidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
