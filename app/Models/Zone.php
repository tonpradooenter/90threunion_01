<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function tables(): HasMany { return $this->hasMany(BookablePlace::class, 'zone', 'code'); }

    protected function priceBaht(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price_satang / 100,
            set: fn ($value) => ['price_satang' => (int) round((float) $value * 100)],
        );
    }
}
