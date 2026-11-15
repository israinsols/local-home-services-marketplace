@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="shell content">
            <span class="eyebrow">{{ $city->country->name }}</span>
            <h1>Home service professionals in {{ $city->name }}</h1>
            <p>Explore local providers in {{ $city->name }}, {{ $city->region }}, or tell us what you need and we will look for a suitable match.</p>
            <a class="button" href="{{ route('leads.create', ['city_id' => $city->id]) }}">Describe your project</a>
        </div>
    </section>
    <section class="section shell">
        <h2>Local providers</h2>
        @if ($providers->count())
            <div class="grid">
                @foreach ($providers as $provider)
                    <a class="card" href="{{ route('providers.show', $provider) }}">
                        <h3>{{ $provider->business_name }}</h3>
                        @if ($provider->is_demo)<span class="badge badge-demo">Demo listing</span>@endif
                        <p class="muted">{{ $provider->categories->pluck('name')->join(', ') }}</p>
                    </a>
                @endforeach
            </div>
            <div class="section">{{ $providers->links() }}</div>
        @else
            <p class="notice">We are welcoming local businesses to this area. Browse services below or request a quote to find a professional.</p>
        @endif
    </section>
    <section class="section shell">
        <h2>Browse services in {{ $city->name }}</h2>
        <div class="grid">
            @foreach ($categories as $category)
                <a class="card" href="{{ route('landing.service-city', [$category->slug, $city->slug]) }}">
                    <h3>{{ $category->name }}</h3>
                    <p class="muted">Explore {{ strtolower($category->name) }} services in {{ $city->name }}.</p>
                </a>
            @endforeach
        </div>
    </section>
@endsection
