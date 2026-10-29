<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cinema extends Model
{
    protected $fillable = ['name', 'address', 'city', 'phone'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function screens(): HasMany
    {
        return $this->hasMany(Screen::class);
    }

    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class);
    }
}
