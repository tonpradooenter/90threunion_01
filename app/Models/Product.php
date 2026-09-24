<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected function priceBaht(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price_satang / 100,
            set: fn ($value) => ['price_satang' => (int) round((float) $value * 100)],
        );
    }

    public function getAvailableAttribute(): int
    {
        return max(0, $this->stock_on_hand - $this->stock_held - $this->stock_sold);
    }
}
