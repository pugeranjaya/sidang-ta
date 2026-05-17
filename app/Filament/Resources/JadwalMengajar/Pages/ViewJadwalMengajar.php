<?php

namespace App\Filament\Resources\JadwalMengajar\Pages;

use App\Filament\Resources\JadwalMengajar\JadwalMengajarResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewJadwalMengajar extends ViewRecord
{
    protected static string $resource = JadwalMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn() => auth()->user()?->isAdmin()),
        ];
    }
}
