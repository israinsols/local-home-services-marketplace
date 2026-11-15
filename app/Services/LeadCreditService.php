<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\Lead;
use App\Models\LeadUnlock;
use App\Models\ProviderLeadMatch;
use App\Models\ProviderProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeadCreditService
{
    public function unlock(int $providerId, int $leadId): LeadUnlock
    {
        return DB::transaction(function () use ($providerId, $leadId) {
            $provider = ProviderProfile::query()->lockForUpdate()->findOrFail($providerId);
            abort_unless($provider->status === 'approved', 403);

            $match = ProviderLeadMatch::query()
                ->where('provider_profile_id', $provider->id)
                ->where('lead_id', $leadId)
                ->lockForUpdate()
                ->firstOrFail();

            $existing = LeadUnlock::query()
                ->where('provider_profile_id', $provider->id)
                ->where('lead_id', $leadId)
                ->first();

            if ($existing) {
                return $existing;
            }

            $lead = Lead::query()->with('category')->lockForUpdate()->findOrFail($leadId);
            $cost = $lead->category->lead_price_credits;

            if ($provider->credit_balance < $cost) {
                throw ValidationException::withMessages([
                    'credits' => "This lead requires {$cost} credits. Add credits to your account to unlock it.",
                ]);
            }

            $provider->decrement('credit_balance', $cost);
            $balance = (int) $provider->fresh()->credit_balance;
            $unlock = LeadUnlock::create([
                'lead_id' => $lead->id,
                'provider_profile_id' => $provider->id,
                'credits_spent' => $cost,
            ]);
            CreditTransaction::create([
                'provider_profile_id' => $provider->id,
                'amount' => -$cost,
                'balance_after' => $balance,
                'type' => 'lead_unlock',
                'description' => "Unlocked {$lead->category->name} lead #{$lead->id}",
                'reference' => "lead:{$lead->id}:provider:{$provider->id}",
                'lead_id' => $lead->id,
            ]);

            $match->update(['status' => 'unlocked']);

            return $unlock;
        });
    }
}
