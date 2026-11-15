<?php

namespace App\Jobs;

use App\Mail\LeadOpportunityMail;
use App\Models\Lead;
use App\Models\ProviderLeadMatch;
use App\Models\ProviderProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DistributeLead implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $leadId)
    {
    }

    public function handle(): void
    {
        $lead = Lead::query()->with('category', 'city')->findOrFail($this->leadId);
        $matchIds = DB::transaction(function () use ($lead) {
            $providers = ProviderProfile::query()
                ->where('status', 'approved')
                ->whereHas('user', fn ($query) => $query->whereNotNull('email_verified_at'))
                ->whereHas('categories', fn ($query) => $query->whereKey($lead->category_id))
                ->whereHas('serviceAreas', fn ($query) => $query->whereKey($lead->city_id))
                ->orderBy('created_at')
                ->limit(5)
                ->get(['id']);

            $ids = [];

            foreach ($providers as $provider) {
                $match = ProviderLeadMatch::firstOrCreate(
                    ['lead_id' => $lead->id, 'provider_profile_id' => $provider->id],
                    ['status' => 'matched'],
                );

                if ($match->wasRecentlyCreated) {
                    $ids[] = $match->id;
                }
            }

            return $ids;
        });

        foreach ($matchIds as $matchId) {
            $match = ProviderLeadMatch::query()->with('provider.user', 'lead.category', 'lead.city')->findOrFail($matchId);
            Mail::to($match->provider->user->email)->queue(new LeadOpportunityMail($match->lead));
            $match->update(['notified_at' => now()]);
        }
    }
}
