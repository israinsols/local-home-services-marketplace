@extends('layouts.app')

@section('content')
    <section class="section shell">
        <div class="content">
            <a href="{{ route('provider.dashboard') }}">← Dashboard</a>
            <span class="eyebrow">Credits and billing</span>
            <h1>Credit balance: {{ number_format($provider->credit_balance) }}</h1>
            @if (session('status'))<p class="notice">{{ session('status') }}</p>@endif
            @error('pack')<p class="error">{{ $message }}</p>@enderror
            <p class="muted">Credits are used to unlock matched lead contact details. Payments are securely processed by Stripe.</p>
            <div class="grid">
                @foreach ($packs as $key => $pack)
                    <form class="card" method="post" action="{{ route('provider.credits.checkout') }}">
                        @csrf
                        <input type="hidden" name="pack" value="{{ $key }}">
                        <h2>{{ ucfirst($key) }}</h2>
                        <p>{{ $pack['credits'] }} credits</p>
                        <p><strong>{{ $currencySymbol }}{{ number_format($pack['price_cents'] / 100, 2) }} {{ $currencyCode }}</strong></p>
                        <button class="button" type="submit">Continue to secure checkout</button>
                    </form>
                @endforeach
            </div>
            <h2>Credit history</h2>
            @forelse ($transactions as $transaction)
                <p>{{ $transaction->created_at->format('M j, Y') }} · {{ $transaction->description }} · {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }} · Balance {{ $transaction->balance_after }}</p>
            @empty
                <p class="muted">No credit transactions yet.</p>
            @endforelse
            {{ $transactions->links() }}
        </div>
    </section>
@endsection
