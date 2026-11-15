@extends('layouts.app')

@section('content')
    <section class="section shell">
        <div class="nav" style="margin-bottom:20px">
            <div><span class="eyebrow">Provider dashboard</span><h1>{{ $provider->business_name }}</h1></div>
            <div class="nav-links">
                <a href="{{ route('provider.profile.edit') }}">Edit profile</a>
                @if ($provider->status === 'approved')<a href="{{ route('provider.credits.index') }}">Credits: {{ $provider->credit_balance }}</a>@endif
                <form method="post" action="{{ route('provider.logout') }}">@csrf<button class="button light" type="submit">Sign out</button></form>
            </div>
        </div>
        @if (session('status'))<p class="notice">{{ session('status') }}</p>@endif
        @if ($provider->status === 'pending')
            <div class="notice"><strong>Application under review.</strong> You can edit your profile while our team reviews your business. Lead opportunities will appear here after approval.</div>
        @elseif ($provider->status === 'suspended')
            <div class="notice"><strong>Account suspended.</strong> Contact support if you believe this is an error.</div>
        @else
            <div class="grid">
                <div class="card"><span class="eyebrow">Available credits</span><h2>{{ number_format($provider->credit_balance) }}</h2><a href="{{ route('provider.credits.index') }}">Buy credits</a></div>
                <div class="card"><span class="eyebrow">New opportunities</span><h2>{{ $matches->total() }}</h2><span class="muted">Matched to your services and areas</span></div>
            </div>
            <h2>Matched leads</h2>
            @if ($matches->count())
                <div class="grid">
                    @foreach ($matches as $match)
                        <a class="card" href="{{ route('provider.leads.show', $match->lead_id) }}">
                            <span class="badge">{{ $match->status === 'unlocked' ? 'Unlocked' : 'New opportunity' }}</span>
                            <h3>{{ $match->lead->category->name }} · {{ $match->lead->city->name }}</h3>
                            <p class="muted">Timeline: {{ str_replace('_', ' ', $match->lead->timeline ?? 'Not specified') }}</p>
                            <p class="muted">Budget: {{ $match->lead->budget ?: 'Not specified' }}</p>
                            <p class="muted">Contact details are {{ $match->status === 'unlocked' ? 'available' : 'hidden until unlocked' }}.</p>
                        </a>
                    @endforeach
                </div>
                <div class="section">{{ $matches->links() }}</div>
            @else
                <p class="notice">No matching leads yet. Keep your service categories and coverage areas up to date.</p>
            @endif
        @endif
        <h2>Recent credit activity</h2>
        @forelse ($transactions as $transaction)
            <p>{{ $transaction->created_at->format('M j, Y') }} · {{ $transaction->description }} · {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }} credits</p>
        @empty
            <p class="muted">No credit transactions yet.</p>
        @endforelse
    </section>
@endsection
