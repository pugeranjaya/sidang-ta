<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                \Filament\Forms\Components\Select::make('role')
                    ->label('Peran')
                    ->options([
                        'admin' => 'Admin',
                        'dosen' => 'Dosen',
                    ])
                    ->required()
                    ->default('dosen'),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->rule(function () {
                        return function (string $attribute, $value, \Closure $fail) {
                            if (!str_ends_with($value, '@upnyk.ac.id')) {
                                $fail('Email wajib menggunakan format @upnyk.ac.id');
                            }
                        };
                    })
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
            ]);
    }
}
