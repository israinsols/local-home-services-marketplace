@extends('layouts.app')

@section('content')
    <div class="shell">
        <nav class="crumbs" aria-label="Breadcrumb">
            @foreach ($breadcrumbs as $crumb)
                @if (! $loop->first)<span aria-hidden="true"> / </span>@endif
                @if ($loop->last)<span aria-current="page">{{ $crumb['name'] }}</span>@else<a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>@endif
            @endforeach
        </nav>
    </div>
    <section class="hero">
        <div class="shell">
            <span class="eyebrow">{{ $city->country->name }} home services</span>
            <h1>{{ $category->name }} in {{ $city->name }}</h1>
            <p>{{ $intro }}</p>
            <a class="button" href="#request-quote">Get a free quote</a>
        </div>
    </section>
    <section id="request-quote" class="section shell">
        <div class="content">
            <h2>Request a free {{ strtolower($category->name) }} quote in {{ $city->name }}</h2>
            <p class="muted">Add a few details about the work and local providers can get in touch.</p>
            <div class="form-card">
                @include('partials.lead-form')
            </div>
        </div>
    </section>
    <section class="section shell">
        <div class="content">
            <h2>Local {{ strtolower($category->name) }} professionals</h2>
            @if ($providers->isNotEmpty())
                <div class="grid">
                    @foreach ($providers as $provider)
                        <a class="card" href="{{ route('providers.show', $provider) }}">
                            <h3>{{ $provider->business_name }}</h3>
                            @if ($provider->is_demo)<span class="badge badge-demo">Demo listing</span>@endif
                            <p class="muted">{{ $provider->description }}</p>
                            @if (! $provider->is_demo)<span class="badge">Verified provider</span>@endif
                        </a>
                    @endforeach
                </div>
            @else
                <p class="notice">There are no approved providers listed in this area yet. You can still send a request and we will let you know if a suitable provider becomes available.</p>
            @endif
        </div>
    </section>
    <section class="section shell">
        <div class="content">
            <h2>Frequently asked questions</h2>
            @foreach ($faqs as $faq)
                <div class="faq">
                    <h3>{{ $faq['question'] }}</h3>
                    <p class="muted">{{ $faq['answer'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
    <section class="section shell">
        <h2>Nearby cities</h2>
        <div class="grid">
            @foreach ($nearbyCities as $nearbyCity)
                <a class="card" href="{{ route('landing.service-city', [$category->slug, $nearbyCity->slug]) }}">{{ $category->name }} in {{ $nearbyCity->name }}</a>
            @endforeach
        </div>
        <h2>Related home services</h2>
        <div class="grid">
            @foreach ($relatedCategories as $relatedCategory)
                <a class="card" href="{{ route('landing.service-city', [$relatedCategory->slug, $city->slug]) }}">{{ $relatedCategory->name }} in {{ $city->name }}</a>
            @endforeach
        </div>
    </section>
    @push('schema')
        @php
            $faqSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => collect($faqs)->map(function ($faq) {
                    return [
                        '@type' => 'Question',
                        'name' => $faq['question'],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
                    ];
                }),
            ];
        @endphp
        <script type="application/ld+json">@json($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @endpush
@endsection
