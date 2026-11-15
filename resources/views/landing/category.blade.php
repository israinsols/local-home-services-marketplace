@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="shell content">
            <span class="eyebrow">Home services</span>
            <h1>Find local {{ strtolower($category->name) }} professionals</h1>
            <p>{{ $category->description }} Compare local providers and request a free, no-obligation quote for your project.</p>
            <a class="button" href="{{ route('leads.create', ['category_id' => $category->id]) }}">Request a quote</a>
        </div>
    </section>
    <section class="section shell">
        <h2>Available {{ strtolower($category->name) }} providers</h2>
        @if ($providers->count())
            <div class="grid">
                @foreach ($providers as $provider)
                    <a class="card" href="{{ route('providers.show', $provider) }}">
                        <h3>{{ $provider->business_name }}</h3>
                        @if ($provider->is_demo)<span class="badge badge-demo">Demo listing</span>@endif
                        <p class="muted">{{ $provider->description }}</p>
                        <p class="muted">Serving {{ $provider->serviceAreas->pluck('name')->join(', ') }}</p>
                    </a>
                @endforeach
            </div>
            <div class="section">{{ $providers->links() }}</div>
        @else
            <p class="notice">Provider listings for this service are being added. Try a nearby area or request a quote and we will look for a match.</p>
        @endif
    </section>
@endsection
