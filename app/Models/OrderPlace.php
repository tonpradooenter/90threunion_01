<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPlace extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    public function place(): BelongsTo { return $this->belongsTo(BookablePlace::class, 'bookable_place_id'); }
}
