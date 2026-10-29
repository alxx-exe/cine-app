<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
{
    protected $fillable = ['screen_id', 'row_label', 'number', 'seat_type'];

    public function screen(): BelongsTo
    {
        return $this->belongsTo(Screen::class);
    }
}
