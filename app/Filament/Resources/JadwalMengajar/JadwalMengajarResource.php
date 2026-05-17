<?php

namespace App\Filament\Resources\JadwalMengajar;

use App\Filament\Resources\JadwalMengajar\Pages\CreateJadwalMengajar;
use App\Filament\Resources\JadwalMengajar\Pages\EditJadwalMengajar;
use App\Filament\Resources\JadwalMengajar\Pages\ListJadwalMengajar;
use App\Filament\Resources\JadwalMengajar\Pages\ViewJadwalMengajar;
use App\Filament\Resources\JadwalMengajar\Schemas\JadwalMengajarForm;
use App\Filament\Resources\JadwalMengajar\Schemas\JadwalMengajarInfolist;
use App\Filament\Resources\JadwalMengajar\Tables\JadwalMengajarTable;
use App\Models\JadwalMengajar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JadwalMengajarResource extends Resource
{
    protected static ?string $model = \App\Models\JadwalMengajar::class;

    protected static ?string $slug = 'jadwal-mengajar';
    protected static ?string $pluralModelLabel = 'Jadwal Mengajar';
    protected static ?string $modelLabel = 'Jadwal Mengajar';
    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = 'icon-jadwal';

    protected static ?string $recordTitleAttribute = 'dosen.nama';

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return JadwalMengajarForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JadwalMengajarInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JadwalMengajarTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJadwalMengajar::route('/'),
            'create' => CreateJadwalMengajar::route('/create'),
            'view' => ViewJadwalMengajar::route('/{record}'),
            'edit' => EditJadwalMengajar::route('/{record}/edit'),
        ];
    }
}
