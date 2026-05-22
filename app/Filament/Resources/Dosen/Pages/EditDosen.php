<?php

namespace App\Filament\Resources\Dosen\Pages;

use App\Filament\Resources\Dosen\DosenResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDosen extends EditRecord
{
    protected static string $resource = DosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['rumpun_keahlian']) && is_array($data['rumpun_keahlian'])) {
            $data['rumpun_keahlian'] = $data['rumpun_keahlian'][0] ?? null;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['rumpun_keahlian'])) {
            $data['rumpun_keahlian'] = (array) $data['rumpun_keahlian'];
        }

        return $data;
    }
}
