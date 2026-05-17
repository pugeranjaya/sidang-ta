<?php

namespace App\Filament\Resources\Dosen\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DosenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('nip')
                    ->required(),
                \Filament\Forms\Components\CheckboxList::make('rumpun_keahlian')
                    ->options([
                        'SPOI' => 'Sistem Produksi & Optimasi Industri (SPOI)',
                        'PSIE' => 'Perancangan Sistem & Inovasi Ekonomi (PSIE)',
                        'KPDP' => 'Kualitas, Perancangan & Daisy Pasok (KPDP)',
                        'RPTE' => 'Rekayasa Produk & Teknik Ergonomi (RPTE)',
                    ])
                    ->required()
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
