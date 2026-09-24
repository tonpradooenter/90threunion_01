<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static ?string $navigationLabel = 'ผู้ใช้และสิทธิ์';
    protected static ?string $modelLabel = 'ผู้ใช้';

    public static function canViewAny(): bool { return auth()->user()?->role === 'super_admin'; }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return static::canViewAny() && $record->id !== auth()->id(); }
    public static function canDelete($record): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('role')->label('หน้าที่')->required()->options([
                'customer' => 'ลูกค้า',
                'shop_admin' => 'ดูแลร้านของที่ระลึก',
                'table_admin' => 'ดูแลโซนและโต๊ะ',
                'finance' => 'ตรวจสลิป',
                'support' => 'ช่วยลูกค้า',
                'scanner' => 'สแกนเข้างาน / จ่ายของ',
                'super_admin' => 'ผู้ดูแลสูงสุด',
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('ชื่อ')->searchable(),
            TextColumn::make('email')->label('อีเมล')->searchable(),
            TextColumn::make('phone')->label('เบอร์โทร')->searchable(),
            TextColumn::make('role')->label('หน้าที่')->badge(),
            TextColumn::make('created_at')->label('สมัครเมื่อ')->dateTime('d/m/Y H:i', 'Asia/Bangkok')->sortable(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array { return ['index' => ManageUsers::route('/')]; }
}
