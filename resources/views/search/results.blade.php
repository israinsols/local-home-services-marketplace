@extends('layouts.app')

@section('content')
    <section class="section shell">
        <div class="content">
            <span class="eyebrow">Search</span>
            <h1>Local home service professionals</h1>
            @if ($providers->count())
                <div class="grid">
                    @foreach ($providers as $provider)
                        <a class="card" href="{{ route('providers.show', $provider) }}">
                            <h2>{{ $provider->business_name }}</h2>
                            @if ($provider->is_demo)<span class="badge badge-demo">Demo listing</span>@endif
                            <p class="muted">{{ $provider->categories->pluck('name')->join(', ') }}</p>
                            <p class="muted">Serving {{ $provider->serviceAreas->pluck('name')->join(', ') }}</p>
                        </a>
                    @endforeach
                </div>
                <div class="section">{{ $providers->links() }}</div>
            @else
                <p class="notice">No providers matched those search terms. Try another area or request a quote and we will look for a match.</p>
            @endif
            <a class="button" href="{{ route('leads.create') }}">Request a quote</a>
        </div>
    </section>
@endsection
