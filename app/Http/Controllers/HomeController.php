<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $catalogue = Cache::remember('home.catalogue.v1', now()->addMinutes(10), fn () => [
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'cities' => City::query()->with('country')->where('is_active', true)->orderBy('name')->get(),
        ]);

        return view('home', $catalogue + [
            'title' => 'Find trusted local home service professionals',
            'description' => 'Tell us about your home project and connect with verified local service professionals across the UK and USA.',
        ]);
    }
}
