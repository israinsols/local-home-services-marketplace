<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountDataController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $user = $request->user()->load([
            'providerProfile.categories:id,name,slug',
            'providerProfile.serviceAreas:id,name,region,country_id',
            'providerProfile.serviceAreas.country:id,code,name',
            'providerProfile.transactions:id,provider_profile_id,amount,balance_after,type,description,reference,created_at',
        ]);

        $data = [
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'business_profile' => $user->providerProfile,
            'exported_at' => now()->toIso8601String(),
        ];

        return response()->streamDownload(
            fn () => print(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)),
            'account-data.json',
            ['Content-Type' => 'application/json; charset=UTF-8', 'Cache-Control' => 'private, no-store'],
        );
    }

    public function erase(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'confirmation' => ['required', 'in:DELETE'],
        ]);

        $user = $request->user();
        $provider = $user->providerProfile;

        DB::transaction(function () use ($user, $provider): void {
            $provider->categories()->detach();
            $provider->serviceAreas()->detach();
            $provider->update([
                'business_name' => 'Removed provider',
                'slug' => 'removed-provider-'.$provider->id,
                'description' => null,
                'phone' => null,
                'website' => null,
                'logo_path' => null,
                'status' => 'suspended',
            ]);
            $user->update([
                'name' => 'Removed user',
                'email' => 'removed-user-'.$user->id.'@deleted.invalid',
                'password' => Str::random(64),
            ]);
            $user->forceFill(['remember_token' => Str::random(60)])->save();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Your account has been removed. Required financial records may be retained where the law requires it.');
    }
}
