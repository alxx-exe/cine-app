<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classification extends Model
{
    protected $fillable = ['code', 'description', 'minimum_age'];

    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
    }
}
