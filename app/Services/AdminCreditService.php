<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AdminCreditService
{
    public function add(ProviderProfile $profile, User $admin, int $credits, string $reason): CreditTransaction
    {
        if ($credits < 1 || $credits > 1_000_000 || mb_strlen($reason) < 3) {
            throw new InvalidArgumentException('Enter a positive credit amount and a reason of at least three characters.');
        }

        return DB::transaction(function () use ($profile, $admin, $credits, $reason) {
            $provider = ProviderProfile::query()->lockForUpdate()->findOrFail($profile->id);
            $provider->increment('credit_balance', $credits);
            $balance = (int) $provider->fresh()->credit_balance;

            return CreditTransaction::create([
                'provider_profile_id' => $provider->id,
                'admin_user_id' => $admin->id,
                'amount' => $credits,
                'balance_after' => $balance,
                'type' => 'admin_adjustment',
                'description' => $reason,
                'reference' => 'admin-credit:'.Str::uuid(),
            ]);
        });
    }
}
