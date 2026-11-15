@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="shell hero-grid">
            <div class="hero-copy">
                <span class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span>Your home, in good hands</span>
                <h1>Find the right help for your next home project.</h1>
                <p>Tell us what you need and where. Explore local professionals and find a good fit for your project.</p>
            <form class="search-form" method="get" action="{{ route('search') }}">
                <div class="field">
                    <label for="category_id">What service do you need?</label>
                    <select id="category_id" name="category_id">
                        <option value="">Choose a service</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="city_id">Where is the project?</label>
                    <select id="city_id" name="city_id">
                        <option value="">Choose a city</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}, {{ $city->country->code }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="button" type="submit">Find professionals</button>
            </form>
                <p class="hero-helper">
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 10 3.2 3.2L15.5 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.5"/></svg>
                    Free to request a quote. No pressure to book.
                </p>
            </div>
            <aside class="hero-panel" aria-label="How it works">
                <div class="hero-panel-heading">
                    <span class="hero-panel-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-7h-4v7H4a1 1 0 0 1-1-1V10Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <div><strong>A simpler way to get started</strong><span>Just three easy steps</span></div>
                </div>
                <span class="eyebrow">How it works</span>
                <ol class="steps">
                    <li><span class="step-number">01</span><div><strong>Choose a service</strong><small>Tell us what kind of help you need.</small></div></li>
                    <li><span class="step-number">02</span><div><strong>Explore local options</strong><small>Find professionals serving your area.</small></div></li>
                    <li><span class="step-number">03</span><div><strong>Pick what works for you</strong><small>Compare and decide at your own pace.</small></div></li>
                </ol>
                <div class="hero-panel-note">
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m10 2 6.5 3v4.6c0 4-2.7 6.8-6.5 8.4-3.8-1.6-6.5-4.4-6.5-8.4V5L10 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="m7 9.8 2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    No charge to request a quote
                </div>
            </aside>
        </div>
    </section>
    <section class="section shell">
        <div class="section-heading">
            <div><span class="eyebrow">Popular services</span><h2>What can we help with?</h2></div>
            <p>From everyday fixes to bigger projects, find the kind of local help you're looking for.</p>
        </div>
        <div class="grid">
            @foreach ($categories as $category)
                <a class="card service-card" href="{{ route('landing.page', $category->slug) }}">
                    <span class="service-card__icon" aria-hidden="true">{{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}</span>
                    <h3>{{ $category->name }}</h3>
                    <p>{{ $category->description }}</p>
                    <span class="service-card__arrow" aria-hidden="true">↗</span>
                </a>
            @endforeach
        </div>
    </section>
    <section class="section section-tint">
        <div class="shell">
            <div class="section-heading">
                <div><span class="eyebrow">Find help nearby</span><h2>Explore your area</h2></div>
                <p>Browse services and professionals available in your city.</p>
            </div>
            <div class="city-list">
            @foreach ($cities as $city)
                <a class="city-link" href="{{ route('landing.page', $city->slug) }}">
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M16 8.3c0 4-6 9-6 9s-6-5-6-9a6 6 0 1 1 12 0Z" stroke="currentColor" stroke-width="1.5"/><circle cx="10" cy="8" r="2" stroke="currentColor" stroke-width="1.5"/></svg>
                    {{ $city->name }}
                </a>
            @endforeach
            </div>
        </div>
    </section>
    <section class="section shell cta-section">
        <div class="cta-panel">
            <div>
                <span class="eyebrow">Ready when you are</span>
                <h2>Make your next home project feel more manageable.</h2>
                <p>Describe what you need and take the next step when it feels right. Requesting a quote is free, and there's no obligation to book.</p>
            </div>
            <a class="button" href="{{ route('leads.create') }}">
                Describe your project
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>
    </section>
@endsection
