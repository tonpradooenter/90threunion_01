<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ManageOrders;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static ?string $navigationLabel = 'รายการสั่งซื้อ';
    protected static ?string $modelLabel = 'รายการสั่งซื้อ';

    public static function canViewAny(): bool { return in_array(auth()->user()?->role, ['super_admin', 'finance', 'support', 'shop_admin', 'table_admin'], true); }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function form(Schema $schema): Schema { return $schema->components([]); }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('id')->label('เลขที่')->sortable(),
            TextColumn::make('user.name')->label('ลูกค้า')->searchable(),
            TextColumn::make('kind')->label('ประเภท')->formatStateUsing(fn ($state) => $state === 'table' ? 'โต๊ะจีน + คอนเสิร์ต' : 'ของที่ระลึก'),
            TextColumn::make('status')->label('สถานะ')->badge(),
            TextColumn::make('total_satang')->label('ยอดเงิน')->formatStateUsing(fn ($state) => number_format($state / 100, 2).' บาท'),
            TextColumn::make('created_at')->label('สร้างเมื่อ')->dateTime('d/m/Y H:i', 'Asia/Bangkok')->sortable(),
        ])->defaultSort('id', 'desc');
    }

    public static function getPages(): array { return ['index' => ManageOrders::route('/')]; }
}
