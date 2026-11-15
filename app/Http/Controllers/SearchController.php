<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\ProviderProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $filters = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        if (! empty($filters['category_id']) && ! empty($filters['city_id'])) {
            $category = Category::query()->whereKey($filters['category_id'])->where('is_active', true)->firstOrFail();
            $city = City::query()->whereKey($filters['city_id'])->where('is_active', true)->firstOrFail();

            return redirect()->route('landing.service-city', [$category->slug, $city->slug]);
        }

        $providers = ProviderProfile::query()
            ->where('status', 'approved')
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->whereHas(
                'categories',
                fn ($categories) => $categories->whereKey($categoryId),
            ))
            ->when($filters['city_id'] ?? null, fn ($query, $cityId) => $query->whereHas(
                'serviceAreas',
                fn ($areas) => $areas->whereKey($cityId),
            ))
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(function ($subquery) use ($term) {
                $subquery->where('business_name', 'like', '%'.$term.'%')
                    ->orWhereHas('serviceAreas', fn ($area) => $area->where('name', 'like', '%'.$term.'%'));
            }))
            ->with(['categories', 'serviceAreas'])
            ->orderBy('business_name')
            ->paginate(12)
            ->withQueryString();

        return view('search.results', [
            'providers' => $providers,
            'title' => 'Search results | '.config('marketplace.name'),
            'description' => 'Search home service professionals on '.config('marketplace.name').'.',
            'indexable' => false,
            'canonical' => route('search'),
        ]);
    }
}
