<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    protected $fillable = ['title', 'synopsis', 'duration_minutes', 'release_date', 'poster_url', 'classification_id', 'is_active'];

    protected function casts(): array
    {
        return ['release_date' => 'date', 'is_active' => 'boolean'];
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function directors(): BelongsToMany
    {
        return $this->belongsToMany(Director::class);
    }

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class)->withPivot('billing_order')->orderByPivot('billing_order');
    }

    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class);
    }
}
