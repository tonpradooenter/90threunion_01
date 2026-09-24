<?php

namespace App\Filament\Resources\BookablePlaces;

use App\Filament\Resources\BookablePlaces\Pages\ManageBookablePlaces;
use App\Models\BookablePlace;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookablePlaceResource extends Resource
{
    protected static ?string $model = BookablePlace::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;
    protected static ?string $navigationLabel = 'โต๊ะรายตัว';
    protected static ?string $modelLabel = 'โต๊ะ';

    public static function canViewAny(): bool { return in_array(auth()->user()?->role, ['super_admin', 'table_admin'], true); }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('zone')->label('โซน')->sortable(),
            TextColumn::make('label')->label('หมายเลขโต๊ะ')->searchable(),
            TextColumn::make('capacity')->label('จำนวนที่นั่ง'),
            IconColumn::make('is_active')->label('เปิดโต๊ะ')->boolean(),
            TextColumn::make('held_by_order_id')->label('กำลังจอง'),
            TextColumn::make('sold_by_order_id')->label('ขายแล้ว'),
        ])->filters([SelectFilter::make('zone')->options(array_combine(range('A','J'), range('A','J')))]);
    }

    public static function getPages(): array { return ['index' => ManageBookablePlaces::route('/')]; }
}
