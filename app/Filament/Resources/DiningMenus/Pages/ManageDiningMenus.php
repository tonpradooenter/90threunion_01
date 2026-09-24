<?php

namespace App\Filament\Resources\DiningMenus\Pages;

use App\Filament\Resources\DiningMenus\DiningMenuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDiningMenus extends ManageRecords
{
    protected static string $resource = DiningMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('เพิ่มชุดอาหาร')];
    }
}
