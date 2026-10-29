<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PriceRule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Screening extends Model
{
    protected $fillable = ['movie_id', 'screen_id', 'tariff_id', 'starts_at', 'format', 'language'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime'];
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function screen(): BelongsTo
    {
        return $this->belongsTo(Screen::class);
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(ReservationSeat::class);
    }

    public function ticketPrice(): ?string
    {
        return PriceRule::where('cinema_id', $this->screen->cinema_id)
            ->where('tariff_id', $this->tariff_id)
            ->where('day_of_week', $this->starts_at->dayOfWeek)
            ->value('amount');
    }
}
