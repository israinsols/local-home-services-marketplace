@extends('layouts.app')

@section('content')
    <section class="section shell">
        <div class="content">
            <a href="{{ route('provider.dashboard') }}">← Dashboard</a>
            <span class="eyebrow">Lead opportunity</span>
            <h1>{{ $lead->category->name }} in {{ $lead->city->name }}</h1>
            @if (session('status'))<p class="notice">{{ session('status') }}</p>@endif
            @if ($errors->has('credits'))<p class="error">{{ $errors->first('credits') }} <a href="{{ route('provider.credits.index') }}">Buy credits</a></p>@endif
            <div class="card">
                <p><strong>Location:</strong> {{ $lead->city->name }}, {{ $lead->city->country->name }}</p>
                <p><strong>Timeline:</strong> {{ str_replace('_', ' ', $lead->timeline ?? 'Not specified') }}</p>
                <p><strong>Budget:</strong> {{ $lead->budget ?: 'Not specified' }}</p>
                @if ($isUnlocked)
                    <h2>Project details</h2>
                    <p>{{ $lead->details }}</p>
                    <h2>Homeowner contact</h2>
                    <p><strong>Name:</strong> {{ $lead->name }}</p>
                    <p><strong>Email:</strong> <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></p>
                    <p><strong>Phone:</strong> <a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a></p>
                    <p><strong>Postcode / ZIP:</strong> {{ $lead->postal_code }}</p>
                @else
                    <p class="notice">Project description and contact details are available after unlocking this lead.</p>
                    <p>This lead costs {{ $lead->category->lead_price_credits }} credits. Your balance is {{ $provider->credit_balance }}.</p>
                    <form method="post" action="{{ route('provider.leads.unlock', $lead->id) }}">
                        @csrf
                        <button class="button" type="submit">Unlock lead</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
