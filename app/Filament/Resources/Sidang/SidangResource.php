<?php

namespace App\Filament\Resources\Sidang;

use App\Filament\Resources\Sidang\Pages\CreateSidang;
use App\Filament\Resources\Sidang\Pages\EditSidang;
use App\Filament\Resources\Sidang\Pages\ListSidang;
use App\Filament\Resources\Sidang\Pages\ViewSidang;
use App\Filament\Resources\Sidang\Schemas\SidangForm;
use App\Filament\Resources\Sidang\Schemas\SidangInfolist;
use App\Filament\Resources\Sidang\Tables\SidangTable;
use App\Models\Sidang;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SidangResource extends Resource
{
    protected static ?string $model = \App\Models\Sidang::class;

    protected static ?string $slug = 'sidang';
    protected static ?string $pluralModelLabel = 'Sidang';
    protected static ?string $modelLabel = 'Sidang';
    protected static ?int $navigationSort = 6;

    protected static string|\BackedEnum|null $navigationIcon = 'icon-sidang';
    protected static ?string $navigationLabel = 'Sidang';

    protected static ?string $recordTitleAttribute = 'mahasiswa.nim';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->isAdmin() || $user?->isDosen();
    }

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

    public static function getNavigationBadge(): ?string
    {
        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['mahasiswa.nama', 'mahasiswa.nim', 'tanggal'];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->isAdmin()) {
            return $query;
        }

        $dosen = \App\Models\Dosen::where('email', $user->email)->first();

        if ($dosen) {
            $dosenId = $dosen->id;
            
            $query->where(function ($q) use ($dosenId) {
                $q->where('penguji1_id', $dosenId)
                  ->orWhere('penguji2_id', $dosenId)
                  ->orWhere('penguji3_id', $dosenId)
                  ->orWhere('penguji4_id', $dosenId)
                  ->orWhere('ketua_sidang_id', $dosenId)
                  ->orWhereHas('mahasiswa', function ($sub) use ($dosenId) {
                      $sub->where('pembimbing_id', $dosenId);
                  });
            });
        } else {
            // If the user is not an admin and no Dosen record matches their email, show nothing.
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return SidangForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SidangInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SidangTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSidang::route('/'),
            'create' => CreateSidang::route('/create'),
            'view' => ViewSidang::route('/{record}'),
            'edit' => EditSidang::route('/{record}/edit'),
        ];
    }
}
