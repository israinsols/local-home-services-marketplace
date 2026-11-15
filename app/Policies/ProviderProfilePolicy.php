<?php

namespace App\Policies;

use App\Models\ProviderProfile;
use App\Models\User;

class ProviderProfilePolicy
{
    public function update(User $user, ProviderProfile $provider): bool
    {
        return $user->role === 'provider' && $provider->user_id === $user->id;
    }
}
