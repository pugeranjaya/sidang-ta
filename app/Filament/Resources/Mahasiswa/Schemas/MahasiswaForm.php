<?php

namespace App\Filament\Resources\Mahasiswa\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MahasiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nim')
                    ->required(),
                TextInput::make('nama')
                    ->required(),
                TextInput::make('judul_ta')
                    ->required(),
                TextInput::make('rumpun_ta')
                    ->required(),
                TextInput::make('pembimbing_id')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
