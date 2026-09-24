<?php

namespace App\Filament\Resources\Zones;

use App\Filament\Resources\Zones\Pages\ManageZones;
use App\Models\Zone;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;
    protected static ?string $navigationLabel = 'เปิดโซน / ตั้งราคา';
    protected static ?string $modelLabel = 'โซน';

    public static function canViewAny(): bool { return in_array(auth()->user()?->role, ['super_admin', 'table_admin'], true); }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return static::canViewAny(); }
    public static function canDelete($record): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('price_baht')->label('ราคาโต๊ะ (บาท)')->required()->numeric()->minValue(0),
            Toggle::make('is_active')->label('เปิดให้จองทั้งโซน')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('โซน')->sortable(),
            TextColumn::make('price_satang')->label('ราคา / โต๊ะ')->formatStateUsing(fn ($state) => number_format($state / 100, 2).' บาท'),
            IconColumn::make('is_active')->label('เปิดจอง')->boolean(),
            TextColumn::make('tables_count')->label('จำนวนโต๊ะ')->counts('tables'),
        ])->defaultSort('sort_order')->recordActions([EditAction::make()]);
    }

    public static function getPages(): array { return ['index' => ManageZones::route('/')]; }
}
