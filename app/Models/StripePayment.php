<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripePayment extends Model
{
    protected $fillable = ['provider_profile_id', 'checkout_session_id', 'stripe_event_id', 'amount_cents', 'currency', 'credits', 'status'];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_profile_id');
    }
}
