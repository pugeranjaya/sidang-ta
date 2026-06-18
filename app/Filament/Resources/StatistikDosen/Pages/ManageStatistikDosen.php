<?php

namespace App\Filament\Resources\StatistikDosen\Pages;

use App\Filament\Resources\StatistikDosen\StatistikDosenResource;
use Filament\Resources\Pages\ManageRecords;

class ManageStatistikDosen extends ManageRecords
{
    protected static string $resource = StatistikDosenResource::class;

    public $filterJenisPeriode = 'bulanan';
    public $filterBulan = 'semua';
    public $filterMinggu = 'semua';
    public $filterTahun = 'semua';

    protected function getHeaderActions(): array
    {
        return [
            // No create action for statistics
        ];
    }

}
