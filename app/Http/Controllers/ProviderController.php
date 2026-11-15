<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use Illuminate\Contracts\View\View;

class ProviderController extends Controller
{
    public function show(ProviderProfile $provider): View
    {
        abort_unless($provider->status === 'approved', 404);
        $provider->load([
            'categories' => fn ($query) => $query->where('is_active', true),
            'serviceAreas' => fn ($query) => $query->where('is_active', true)->with('country'),
        ]);
        $reviews = $provider->reviews()->where('status', 'approved')->latest()->paginate(10);
        $rating = $provider->average_rating;
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $provider->business_name,
            'description' => $provider->description,
            'url' => route('providers.show', $provider),
            'areaServed' => $provider->serviceAreas->map(fn ($city) => ['@type' => 'City', 'name' => $city->name])->values(),
            'knowsAbout' => $provider->categories->pluck('name')->values(),
        ];

        if ($provider->phone) {
            $schema['telephone'] = $provider->phone;
        }

        if ($reviews->total() > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round($rating, 1),
                'reviewCount' => $reviews->total(),
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }

        return view('providers.show', [
            'provider' => $provider,
            'reviews' => $reviews,
            'schema' => $schema,
            'title' => $provider->business_name.' | Local Home Services',
            'description' => str($provider->description)->stripTags()->limit(155)->toString(),
            'indexable' => filled($provider->description) && $provider->categories->isNotEmpty() && $provider->serviceAreas->isNotEmpty(),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => $provider->business_name, 'url' => route('providers.show', $provider)],
            ],
        ]);
    }
}
