<?php

namespace App\Filament\Resources\PengecualianJadwals;

use App\Filament\Resources\PengecualianJadwals\Pages\CreatePengecualianJadwal;
use App\Filament\Resources\PengecualianJadwals\Pages\EditPengecualianJadwal;
use App\Filament\Resources\PengecualianJadwals\Pages\ListPengecualianJadwals;
use App\Filament\Resources\PengecualianJadwals\Schemas\PengecualianJadwalForm;
use App\Filament\Resources\PengecualianJadwals\Tables\PengecualianJadwalsTable;
use App\Models\PengecualianJadwal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PengecualianJadwalResource extends Resource
{
    protected static ?string $model = PengecualianJadwal::class;

    protected static ?string $modelLabel = 'Pengecualian Jadwal';
    protected static ?string $pluralModelLabel = 'Pengecualian Jadwal';
    protected static ?string $slug = 'pengecualian-jadwal';
    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $recordTitleAttribute = 'tanggal';

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return PengecualianJadwalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengecualianJadwalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengecualianJadwals::route('/'),
            'create' => CreatePengecualianJadwal::route('/create'),
            'edit' => EditPengecualianJadwal::route('/{record}/edit'),
        ];
    }
}
