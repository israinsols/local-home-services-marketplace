<?php

namespace App\Services;

use App\Models\ProviderProfile;
use Illuminate\Support\Facades\DB;
use DomainException;

class ProviderApprovalService
{
    public function approve(ProviderProfile $profile): ProviderProfile
    {
        return DB::transaction(function () use ($profile) {
            $provider = ProviderProfile::query()->lockForUpdate()->with('user')->findOrFail($profile->id);

            if (! $provider->user->hasVerifiedEmail()) {
                throw new DomainException('The provider must verify their email before approval.');
            }

            $provider->update(['status' => 'approved', 'approved_at' => now()]);

            return $provider->refresh();
        });
    }

    public function reject(ProviderProfile $profile): ProviderProfile
    {
        return $this->setStatus($profile, 'rejected');
    }

    public function suspend(ProviderProfile $profile): ProviderProfile
    {
        return $this->setStatus($profile, 'suspended');
    }

    private function setStatus(ProviderProfile $profile, string $status): ProviderProfile
    {
        return DB::transaction(function () use ($profile, $status) {
            $provider = ProviderProfile::query()->lockForUpdate()->findOrFail($profile->id);
            $provider->update([
                'status' => $status,
                'approved_at' => $status === 'approved' ? ($provider->approved_at ?? now()) : null,
            ]);

            return $provider->refresh();
        });
    }
}
