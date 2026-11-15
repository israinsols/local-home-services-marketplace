<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Lead;
use App\Jobs\DistributeLead;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LeadController extends Controller
{
    public function create(Request $request): View
    {
        return view('leads.create', [
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'cities' => City::query()->with('country')->where('is_active', true)->orderBy('name')->get(),
            'selectedCategory' => $request->integer('category_id'),
            'selectedCity' => $request->integer('city_id'),
            'title' => 'Request a free home service quote',
            'description' => 'Tell us about your project and connect with matching home service professionals.',
            'indexable' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'phone' => ['required', 'string', 'regex:/^\+?[\d\s().-]{7,24}$/'],
            'postal_code' => ['required', 'string', 'max:16'],
            'details' => ['required', 'string', 'min:20', 'max:5000'],
            'timeline' => ['nullable', 'in:asap,within_2_weeks,within_1_month,flexible'],
            'budget' => ['nullable', 'string', 'max:60'],
            'consent' => ['accepted'],
            'marketing_consent' => ['sometimes', 'accepted'],
            'website' => ['prohibited'],
        ]);

        $category = Category::query()->whereKey($data['category_id'])->where('is_active', true)->firstOrFail();
        $city = City::query()->with('country')->whereKey($data['city_id'])->where('is_active', true)->firstOrFail();

        if ($city->country->code === 'GB') {
            $validPostalCode = preg_match('/^[A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2}$/i', $data['postal_code']) === 1;
        } else {
            $validPostalCode = preg_match('/^\d{5}(?:-\d{4})?$/', $data['postal_code']) === 1;
        }

        if (! $validPostalCode) {
            throw ValidationException::withMessages([
                'postal_code' => "Enter a valid {$city->country->postal_label}.",
            ]);
        }

        $lead = Lead::create([
            'category_id' => $category->id,
            'city_id' => $city->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'postal_code' => strtoupper(trim($data['postal_code'])),
            'details' => $data['details'],
            'timeline' => $data['timeline'] ?? null,
            'budget' => $data['budget'] ?? null,
            'marketing_consent' => isset($data['marketing_consent']),
            'consent_at' => now(),
            'consent_version' => config('marketplace.lead_consent_version'),
            'status' => 'new',
        ]);
        DistributeLead::dispatch($lead->id)->afterCommit();

        return redirect()->route('leads.thank-you');
    }

    public function thankYou(): View
    {
        return view('leads.thank-you', [
            'title' => 'Request received | '.config('marketplace.name'),
            'description' => 'Your home service request has been received.',
            'indexable' => false,
        ]);
    }
}
