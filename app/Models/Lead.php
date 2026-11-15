<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $fillable = [
        'category_id', 'city_id', 'name', 'email', 'phone', 'postal_code', 'details',
        'timeline', 'budget', 'marketing_consent', 'consent_at', 'consent_version', 'status',
    ];

    protected function casts(): array
    {
        return ['marketing_consent' => 'boolean', 'consent_at' => 'datetime'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(ProviderLeadMatch::class);
    }

    public function unlocks(): HasMany
    {
        return $this->hasMany(LeadUnlock::class);
    }
}
