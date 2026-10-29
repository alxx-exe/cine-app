<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceRule extends Model
{
    protected $fillable = ['cinema_id', 'tariff_id', 'day_of_week', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'day_of_week' => 'integer'];
    }

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class);
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }
}
