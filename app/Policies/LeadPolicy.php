<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function view(User $user, Lead $lead): bool
    {
        $provider = $user->providerProfile;

        return $provider !== null
            && $provider->status === 'approved'
            && $provider->matches()->where('lead_id', $lead->id)->exists();
    }

    public function unlock(User $user, Lead $lead): bool
    {
        return $this->view($user, $lead);
    }
}
