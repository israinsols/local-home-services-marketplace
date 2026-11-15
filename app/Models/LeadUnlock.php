<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadUnlock extends Model
{
    protected $fillable = ['lead_id', 'provider_profile_id', 'credits_spent'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_profile_id');
    }
}
