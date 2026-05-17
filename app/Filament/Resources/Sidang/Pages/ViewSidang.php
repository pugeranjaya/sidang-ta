<?php

namespace App\Filament\Resources\Sidang\Pages;

use App\Filament\Resources\Sidang\SidangResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSidang extends ViewRecord
{
    protected static string $resource = SidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn() => auth()->user()?->isAdmin()),
        ];
    }
}
