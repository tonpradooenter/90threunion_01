<?php

namespace App\Filament\Resources\DiningMenus;

use App\Filament\Resources\DiningMenus\Pages\ManageDiningMenus;
use App\Models\DiningMenu;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DiningMenuResource extends Resource
{
    protected static ?string $model = DiningMenu::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static ?string $navigationLabel = 'ชุดอาหารโต๊ะจีน';
    protected static ?string $modelLabel = 'ชุดอาหาร';

    public static function canViewAny(): bool { return in_array(auth()->user()?->role, ['super_admin', 'table_admin'], true); }
    public static function canCreate(): bool { return static::canViewAny(); }
    public static function canEdit($record): bool { return static::canViewAny(); }
    public static function canDelete($record): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('ชื่อชุดอาหาร')->required()->maxLength(255),
            Textarea::make('description')->label('รายการอาหาร / คำอธิบาย')->maxLength(2000)->columnSpanFull(),
            TextInput::make('sort_order')->label('ลำดับแสดง')->required()->integer()->minValue(1)->maxValue(255)->default(1),
            Toggle::make('is_active')->label('เปิดให้เลือก')->required()->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('ชุดอาหาร')->searchable(),
            TextColumn::make('description')->label('รายละเอียด')->limit(70),
            IconColumn::make('is_active')->label('เปิดให้เลือก')->boolean(),
        ])->defaultSort('sort_order')->recordActions([EditAction::make()]);
    }

    public static function getPages(): array { return ['index' => ManageDiningMenus::route('/')]; }
}
