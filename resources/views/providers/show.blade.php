@extends('layouts.app')

@section('content')
    <div class="shell">
        <nav class="crumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a> / <span aria-current="page">{{ $provider->business_name }}</span>
        </nav>
    </div>
    <section class="hero">
        <div class="shell content">
            @if ($provider->is_demo)
                <span class="badge badge-demo">Demo listing — sample data, not a real business</span>
            @else
                <span class="badge">Verified provider</span>
            @endif
            <h1>{{ $provider->business_name }}</h1>
            @if ($provider->description)<p>{{ $provider->description }}</p>@endif
            <p class="muted">Services: {{ $provider->categories->pluck('name')->join(', ') }}</p>
            <p class="muted">Areas: {{ $provider->serviceAreas->map(fn ($area) => $area->name.', '.$area->country->code)->join(' · ') }}</p>
            @if ($provider->average_rating > 0)
                <p><strong>{{ number_format($provider->average_rating, 1) }} / 5</strong> from {{ $reviews->total() }} approved reviews</p>
            @endif
            @if ($provider->website)
                <p><a href="{{ $provider->website }}" rel="nofollow noopener" target="_blank">Visit business website</a></p>
            @endif
            <a class="button" href="{{ route('leads.create') }}">Request a free quote</a>
        </div>
    </section>
    <section class="section shell">
        <div class="content">
            <h2>Customer reviews</h2>
            @forelse ($reviews as $review)
                <article class="card" style="margin-bottom:14px">
                    <p><strong>{{ $review->author_name }}</strong> · {{ $review->rating }} / 5</p>
                    <p class="muted">{{ $review->body }}</p>
                </article>
            @empty
                <p class="muted">{{ $provider->is_demo ? 'Demo listings do not include customer reviews.' : 'No approved reviews yet.' }}</p>
            @endforelse
            {{ $reviews->links() }}
        </div>
    </section>
@endsection
