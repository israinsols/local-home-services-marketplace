<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Lead;
use App\Models\ProviderProfile;
use App\Services\LeadCreditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProviderPortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $provider = $request->user()->providerProfile;
        $matches = $provider->status === 'approved'
            ? $provider->matches()->with('lead.category', 'lead.city.country')->latest()->paginate(15)
            : null;

        return view('provider.dashboard', [
            'provider' => $provider,
            'matches' => $matches,
            'transactions' => $provider->transactions()->latest()->limit(8)->get(),
            'title' => 'Provider dashboard | '.config('marketplace.name'),
            'description' => 'Manage your local home service lead opportunities.',
            'indexable' => false,
        ]);
    }

    public function showLead(Request $request, int $leadId): View
    {
        $provider = $request->user()->providerProfile;
        abort_unless($provider->status === 'approved', 403);
        $this->authorize('view', Lead::findOrFail($leadId));
        $match = $provider->matches()
            ->where('lead_id', $leadId)
            ->with('lead.category', 'lead.city.country')
            ->firstOrFail();

        return view('provider.leads.show', [
            'provider' => $provider,
            'match' => $match,
            'lead' => $match->lead,
            'isUnlocked' => $match->status === 'unlocked',
            'title' => 'Lead opportunity | '.config('marketplace.name'),
            'description' => 'View a local home service lead opportunity.',
            'indexable' => false,
        ]);
    }

    public function unlockLead(Request $request, int $leadId, LeadCreditService $credits): RedirectResponse
    {
        $provider = $request->user()->providerProfile;
        $this->authorize('unlock', Lead::findOrFail($leadId));
        $credits->unlock($provider->id, $leadId);

        return redirect()->route('provider.leads.show', $leadId)->with('status', 'Lead contact details unlocked.');
    }

    public function editProfile(Request $request): View
    {
        $provider = $request->user()->providerProfile;
        $this->authorize('update', $provider);

        return view('provider.profile.edit', [
            'provider' => $provider->load('categories', 'serviceAreas'),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'cities' => City::query()->with('country')->where('is_active', true)->orderBy('name')->get(),
            'title' => 'Edit business profile | '.config('marketplace.name'),
            'description' => 'Update your public service provider profile and coverage areas.',
            'indexable' => false,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $provider = $request->user()->providerProfile;
        $this->authorize('update', $provider);
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:3000'],
            'phone' => ['required', 'string', 'regex:/^\+?[\d\s().-]{7,24}$/'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'category_ids' => ['required', 'array', 'min:1', 'max:10'],
            'category_ids.*' => ['integer', 'distinct', Rule::exists('categories', 'id')->where('is_active', true)],
            'city_ids' => ['required', 'array', 'min:1', 'max:50'],
            'city_ids.*' => ['integer', 'distinct', Rule::exists('cities', 'id')->where('is_active', true)],
        ]);

        DB::transaction(function () use ($provider, $data) {
            $provider->update([
                'business_name' => $data['business_name'],
                'description' => $data['description'] ?? null,
                'phone' => $data['phone'],
                'website' => $data['website'] ?? null,
            ]);
            $provider->categories()->sync($data['category_ids']);
            $provider->serviceAreas()->sync($data['city_ids']);
        });

        return redirect()->route('provider.profile.edit')->with('status', 'Your business profile has been updated.');
    }
}
