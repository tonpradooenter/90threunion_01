<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookablePlace extends Model
{
    protected $guarded = [];

    public function zoneDefinition(): BelongsTo { return $this->belongsTo(Zone::class, 'zone', 'code'); }
}
