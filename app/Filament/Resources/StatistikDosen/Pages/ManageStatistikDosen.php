<?php

namespace App\Filament\Resources\StatistikDosen\Pages;

use App\Filament\Resources\StatistikDosen\StatistikDosenResource;
use Filament\Resources\Pages\ManageRecords;

class ManageStatistikDosen extends ManageRecords
{
    protected static string $resource = StatistikDosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for statistics
        ];
    }
}
