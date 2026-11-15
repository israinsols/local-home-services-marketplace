<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['country_id', 'name', 'slug', 'region', 'postal_prefix', 'population', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'population' => 'integer'];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(ProviderProfile::class, 'provider_service_areas');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
