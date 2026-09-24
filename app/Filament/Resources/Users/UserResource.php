<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static ?string $navigationLabel = 'บัญชีเจ้าหน้าที่';
    protected static ?string $modelLabel = 'บัญชีเจ้าหน้าที่';

    private const STAFF_ROLES = [
        'shop_admin' => 'ดูแลร้านของที่ระลึก',
        'table_admin' => 'ดูแลโซน โต๊ะ และชุดอาหาร',
        'finance' => 'ตรวจสอบสลิป',
        'support' => 'ช่วยลูกค้าสั่งซื้อ',
        'scanner' => 'สแกนบัตรเข้างานและจ่ายของ',
    ];

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny()
            && array_key_exists($record->role, self::STAFF_ROLES)
            && $record->id !== auth()->id();
    }

    public static function canDelete($record): bool
    {
        return static::canEdit($record);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('role', array_keys(self::STAFF_ROLES));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('ชื่อที่แสดง')->required()->maxLength(120),
            TextInput::make('username')->label('ชื่อผู้ใช้')
                ->required()->minLength(4)->maxLength(50)
                ->regex('/^[a-z0-9._-]+$/')->unique(ignoreRecord: true)
                ->helperText('ใช้ตัวพิมพ์เล็กอังกฤษ ตัวเลข จุด ขีดกลาง หรือขีดล่าง'),
            Select::make('role')->label('หน้าที่')->required()->options(self::STAFF_ROLES),
            TextInput::make('password')->label('รหัสผ่านใหม่')
                ->password()->minLength(10)->maxLength(255)
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->helperText('ตอนแก้ไข: เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('ชื่อ')->searchable(),
            TextColumn::make('username')->label('ชื่อผู้ใช้')->searchable()->copyable(),
            TextColumn::make('role')->label('หน้าที่')->badge()
                ->formatStateUsing(fn (string $state): string => self::STAFF_ROLES[$state] ?? $state),
            TextColumn::make('created_at')->label('เพิ่มเมื่อ')->dateTime('d/m/Y H:i', 'Asia/Bangkok')->sortable(),
        ])->recordActions([
            EditAction::make()->label('แก้ไข / รีเซ็ตรหัส')
                ->after(fn (User $record) => DB::table('sessions')->where('user_id', $record->id)->delete()),
            DeleteAction::make()->label('ลบบัญชี')->requiresConfirmation()
                ->after(fn (User $record) => DB::table('sessions')->where('user_id', $record->id)->delete()),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageUsers::route('/')];
    }
}
