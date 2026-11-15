<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\ContentPage;
use App\Models\ProviderProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class LandingPageController extends Controller
{
    private const INTRODUCTIONS = [
        'Compare established local {service} professionals in {city}. Share a few details about your project and interested businesses can respond with a quote.',
        'Planning a {service} project in {city}? Describe the work once and hear from service providers who cover your {area}.',
        'Find help for your next {city} {service} project. Our local marketplace helps you connect with relevant businesses without an obligation to hire.',
        'From small repairs to larger improvements, discover {service} professionals serving homes in {city}. Tell us what you need to get started.',
    ];

    public function serviceAndCity(string $service, string $city): View|Response
    {
        $category = Category::query()->where('slug', $service)->where('is_active', true)->firstOrFail();
        $location = City::query()->with('country')->where('slug', $city)->where('is_active', true)->firstOrFail();
        $providers = $this->providersFor($category, $location);

        return response()->view('landing.service-city', [
            'category' => $category,
            'city' => $location,
            'providers' => $providers,
            'nearbyCities' => City::query()->where('country_id', $location->country_id)->where('id', '!=', $location->id)->where('is_active', true)->orderBy('name')->limit(6)->get(),
            'relatedCategories' => Category::query()->where('id', '!=', $category->id)->where('is_active', true)->orderBy('name')->limit(6)->get(),
            'intro' => $this->introduction($category, $location),
            'faqs' => $this->faqs($category, $location),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'cities' => City::query()->with('country')->where('is_active', true)->orderBy('name')->get(),
            'title' => "{$category->name} in {$location->name} | Find Local Professionals",
            'description' => "Find trusted {$category->name} professionals in {$location->name}. Describe your project and connect with local businesses for free.",
            'indexable' => $providers->total() > 0,
            'schema' => $this->serviceSchema($category, $location),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => $category->name, 'url' => route('landing.page', $category->slug)],
                ['name' => $location->name, 'url' => route('landing.service-city', [$category->slug, $location->slug])],
            ],
        ], 200, [
            'X-Robots-Tag' => $providers->total() > 0 ? 'index, follow' : 'noindex, follow',
        ]);
    }

    public function show(string $slug): View
    {
        $category = Category::query()->where('slug', $slug)->where('is_active', true)->first();

        if ($category) {
            $providers = ProviderProfile::query()
                ->where('status', 'approved')
                ->whereHas('categories', fn ($query) => $query->whereKey($category->id))
                ->with('serviceAreas.country')
                ->withCount(['reviews as approved_reviews_count' => fn ($query) => $query->where('status', 'approved')])
                ->orderBy('business_name')
                ->paginate(12);

            return view('landing.category', [
                'category' => $category,
                'providers' => $providers,
                'title' => "Local {$category->name} Professionals | ".config('marketplace.name'),
                'description' => "Browse local {$category->name} professionals and request a free quote for your home project.",
                'indexable' => $providers->total() > 0,
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => route('home')],
                    ['name' => $category->name, 'url' => route('landing.page', $category->slug)],
                ],
            ]);
        }

        $city = City::query()->with('country')->where('slug', $slug)->where('is_active', true)->first();

        if ($city) {
            $providers = ProviderProfile::query()
                ->where('status', 'approved')
                ->whereHas('serviceAreas', fn ($query) => $query->whereKey($city->id))
                ->with('categories')
                ->orderBy('business_name')
                ->paginate(12);

            return view('landing.city', [
                'city' => $city,
                'providers' => $providers,
                'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
                'title' => "Home Service Professionals in {$city->name} | ".config('marketplace.name'),
                'description' => "Find trusted home service professionals in {$city->name}, {$city->country->name}. Request quotes at no cost.",
                'indexable' => $providers->total() > 0,
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => route('home')],
                    ['name' => $city->name, 'url' => route('landing.page', $city->slug)],
                ],
            ]);
        }

        $page = ContentPage::query()->where('slug', $slug)->where('status', 'published')->firstOrFail();

        return view('pages.show', [
            'page' => $page,
            'renderedBody' => Str::markdown($page->body, ['html_input' => 'strip']),
            'title' => $page->meta_title ?: $page->title,
            'description' => $page->meta_description ?: str($page->body)->stripTags()->limit(155),
            'indexable' => filled($page->body),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => $page->title, 'url' => route('landing.page', $page->slug)],
            ],
        ]);
    }

    private function providersFor(Category $category, City $city)
    {
        return ProviderProfile::query()
            ->where('status', 'approved')
            ->whereHas('categories', fn ($query) => $query->whereKey($category->id))
            ->whereHas('serviceAreas', fn ($query) => $query->whereKey($city->id))
            ->with('categories')
            ->withCount(['reviews as approved_reviews_count' => fn ($query) => $query->where('status', 'approved')])
            ->orderBy('business_name')
            ->paginate(12);
    }

    private function introduction(Category $category, City $city): string
    {
        $index = crc32($category->slug.'-'.$city->slug) % count(self::INTRODUCTIONS);

        return str_replace(
            ['{service}', '{city}', '{area}'],
            [strtolower($category->name), $city->name, $city->country->code === 'US' ? 'neighborhood' : 'neighbourhood'],
            self::INTRODUCTIONS[$index],
        );
    }

    private function faqs(Category $category, City $city): array
    {
        return [
            ['question' => "How do I find a {$category->name} professional in {$city->name}?", 'answer' => 'Tell us a little about the work you need. We will share your request with matching local professionals so they can decide whether to contact you.'],
            ['question' => 'Is it free to request a quote?', 'answer' => 'Yes. Homeowners can submit a request at no charge and are not obliged to hire any business that responds.'],
            ['question' => 'How are my contact details used?', 'answer' => 'Your details are shared only with providers selected for your request. See our privacy policy for information about your rights and data retention.'],
        ];
    }

    private function serviceSchema(Category $category, City $city): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => "{$category->name} services in {$city->name}",
            'serviceType' => $category->name,
            'areaServed' => ['@type' => 'City', 'name' => $city->name],
            'provider' => ['@type' => 'Organization', 'name' => config('app.name')],
        ];
    }
}
