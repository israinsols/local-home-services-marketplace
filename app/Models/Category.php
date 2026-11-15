<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'lead_price_credits', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'lead_price_credits' => 'integer'];
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(ProviderProfile::class, 'provider_category');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
