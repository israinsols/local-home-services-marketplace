<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProviderProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'business_name', 'slug', 'description', 'phone', 'website', 'logo_path', 'status', 'credit_balance', 'approved_at', 'is_demo',
    ];

    protected function casts(): array
    {
        return ['credit_balance' => 'integer', 'approved_at' => 'datetime', 'is_demo' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'provider_category');
    }

    public function serviceAreas(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'provider_service_areas');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(ProviderLeadMatch::class);
    }

    public function unlocks(): HasMany
    {
        return $this->hasMany(LeadUnlock::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(StripePayment::class);
    }

    public function getAverageRatingAttribute(): float
    {
        return (float) $this->reviews()->where('status', 'approved')->avg('rating');
    }
}
