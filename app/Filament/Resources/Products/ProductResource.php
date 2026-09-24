<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'ของที่ระลึก';
    protected static ?string $modelLabel = 'สินค้า';
    protected static ?string $pluralModelLabel = 'สินค้า';

    public static function canViewAny(): bool { return in_array(auth()->user()?->role, ['super_admin', 'shop_admin'], true); }
    public static function canCreate(): bool { return static::canViewAny(); }
    public static function canEdit($record): bool { return static::canViewAny(); }
    public static function canDelete($record): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('kind')->default('souvenir'),
                TextInput::make('name')
                    ->label('ชื่อสินค้า')->required()->maxLength(255),
                Textarea::make('description')
                    ->label('รายละเอียด')->columnSpanFull(),
                FileUpload::make('image_path')->label('รูปสินค้า')->image()->maxSize(5120)->disk('private')->directory('products')->visibility('private'),
                TextInput::make('price_baht')->label('ราคา (บาท)')->required()->numeric()->minValue(0),
                Toggle::make('is_active')
                    ->label('เปิดขาย')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('สินค้า')->searchable(),
                TextColumn::make('price_satang')
                    ->label('ราคา')->formatStateUsing(fn ($state) => number_format($state / 100, 2).' บาท')->sortable(),
                TextColumn::make('stock_on_hand')
                    ->label('รับเข้าทั้งหมด')->numeric()->sortable(),
                TextColumn::make('stock_held')
                    ->label('กันจอง')->numeric()->sortable(),
                TextColumn::make('stock_sold')
                    ->label('ขายแล้ว')->numeric()->sortable(),
                IconColumn::make('is_active')
                    ->label('เปิดขาย')->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('restock')->label('รับสินค้าเข้า')->form([
                    TextInput::make('quantity')->label('จำนวนที่รับเข้า')->required()->integer()->minValue(1)->maxValue(100000),
                ])->action(function (Product $record, array $data): void {
                    DB::transaction(fn () => Product::whereKey($record->id)->lockForUpdate()->firstOrFail()->increment('stock_on_hand', (int) $data['quantity']));
                }),
            ])
            ;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProducts::route('/'),
        ];
    }
}
